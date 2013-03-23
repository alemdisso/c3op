<?php

class C3op_Resources_ResponsibleMapper {

    protected $db;
    protected $identityMap;

    function __construct() {
        $this->db = Zend_Registry::get('db');
        $this->identityMap = new SplObjectStorage;
    }

    public function getAllIds() {
        $result = array();
            foreach ($this->db->query('SELECT id FROM resources_responsibles;') as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function insert(C3op_Resources_Responsible $new) {

        $query = $this->db->prepare("INSERT INTO resources_responsibles (
            project, action, type, institution, contact, value, status)
            VALUES (:project, :action, :type, :institution, :contact, :value, :status)");

        $query->bindValue(':project', $new->GetProject(), PDO::PARAM_INT);
        $query->bindValue(':action', $new->GetAction(), PDO::PARAM_INT);
        $query->bindValue(':type', $new->getType(), PDO::PARAM_INT);
        $query->bindValue(':institution', $new->GetInstitution(), PDO::PARAM_STR);
        $query->bindValue(':contact', $new->GetContact(), PDO::PARAM_STR);
        $query->bindValue(':value', $new->GetValue(), PDO::PARAM_STR);
        $query->bindValue(':status', $new->GetStatus(), PDO::PARAM_INT);

        $query->execute();

        $new->SetId((int)$this->db->lastInsertId());
        $this->identityMap[$new] = $new->GetId();

    }

    public function update(C3op_Resources_Responsible $obj) {
        if (!isset($this->identityMap[$obj])) {
            throw new C3op_Resources_ResponsibleMapperException('Object has no ID, cannot update.');
        }

        $query = $this->db->prepare("UPDATE resources_responsibles
            SET project = :project, action = :action, type = :type,
            institution = :institution, contact = :contact,
            value = :value, status = :status WHERE id = :id;");

        $query->bindValue(':project', $obj->GetProject(), PDO::PARAM_STR);
        $query->bindValue(':action', $obj->GetAction(), PDO::PARAM_STR);
        $query->bindValue(':type', $obj->GetType(), PDO::PARAM_STR);
        $query->bindValue(':institution', $obj->getInstitution(), PDO::PARAM_STR);
        $query->bindValue(':contact', $obj->getContact(), PDO::PARAM_STR);
        $query->bindValue(':value', $obj->GetValue(), PDO::PARAM_STR);
        $query->bindValue(':status', $obj->GetStatus(), PDO::PARAM_STR);
        $query->bindValue(':id', $this->identityMap[$obj], PDO::PARAM_STR);

        try {
            $query->execute();
        } catch (Exception $e) {
            throw new C3op_Resources_ResponsibleException("$sql failed");
        }

    }

    public function findById($id) {
        $this->identityMap->rewind();
        while ($this->identityMap->valid()) {
            if ($this->identityMap->getInfo() == $id) {
                return $this->identityMap->current();
            }
            $this->identityMap->next();
        }

        $query = $this->db->prepare('SELECT project, action, type, institution, contact, value, status FROM resources_responsibles WHERE id = :id;');
        $query->bindValue(':id', $id, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();

        if (empty($result)) {
            throw new C3op_Resources_ResponsibleMapperException(sprintf('There is no Human Resource with id #%d.', $id));
        }
        $obj = new C3op_Resources_Responsible();

        $this->setAttributeValue($obj, $id, 'id');


        $this->setAttributeValue($obj, $result['project'], 'project');
        $this->setAttributeValue($obj, $result['action'], 'action');
        $this->setAttributeValue($obj, $result['type'], 'type');
        $this->setAttributeValue($obj, $result['institution'], 'institution');
        $this->setAttributeValue($obj, $result['contact'], 'contact');
        $this->setAttributeValue($obj, $result['value'], 'value');
        $this->setAttributeValue($obj, $result['status'], 'status');

        $this->identityMap[$obj] = $id;

        return $obj;

        $this->FetchDates($obj);
    }

    public function delete(C3op_Resources_Responsible $i) {
        if (!isset($this->identityMap[$i])) {
            throw new C3op_Resources_ResponsibleMapperException('Object has no ID, cannot delete.');
        }
        $this->db->exec(
            sprintf(
                'DELETE FROM resources_responsibles WHERE id = %d;',
                $this->identityMap[$i]
            )
        );
        unset($this->identityMap[$i]);
    }

     public function getResponsibleForAction(C3op_Projects_Action $a) {
        $result = array();
            foreach ($this->db->query(
                    sprintf('SELECT id FROM resources_responsibles WHERE action = %d LIMIT 1;', $a->GetId())) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

   private function setAttributeValue(C3op_Resources_Responsible $i, $fieldValue, $attributeName)
    {
        $attribute = new ReflectionProperty($i, $attributeName);
        $attribute->setAccessible(TRUE);
        $attribute->setValue($i, $fieldValue);
    }

    public function getAllActionsEngaging(C3op_Register_Contact $obj, C3op_Projects_Project $project)
    {
        $query = $this->db->prepare('SELECT a.id as id, r.id as responsibleId
                    FROM projects_actions a
                    LEFT JOIN resources_responsibles r ON a.id = r.action
                    LEFT JOIN register_contacts t ON r.contact = r.id
                    WHERE a.project = :project AND r.contact = :contact
                    AND (r.status = :foreseen OR r.status = :contracted OR r.status = :acquitted);');
        $query->bindValue(':project', $project->GetId(), PDO::PARAM_STR);
        $query->bindValue(':contact', $obj->GetId(), PDO::PARAM_STR);
        $query->bindValue(':foreseen', C3op_Resources_ResponsibleStatusConstants::STATUS_FORESEEN, PDO::PARAM_STR);
        $query->bindValue(':contracted', C3op_Resources_ResponsibleStatusConstants::STATUS_CONTRACTED, PDO::PARAM_STR);
        $query->bindValue(':acquitted', C3op_Resources_ResponsibleStatusConstants::STATUS_ACQUITTED, PDO::PARAM_STR);
        $query->execute();
        $resultPDO = $query->fetchAll();

        $result = array();
        foreach ($resultPDO as $row) {
            $result[$row['id']] = array(
                'responsible' => $row['responsibleId'],
            );
        }
        return $result;
    }
    public function getAllOutlays(C3op_Resources_Responsible $obj)
    {
        $result = array();
        foreach ($this->db->query(
                sprintf(
                    'SELECT id FROM finances_outlays WHERE responsible = %d;',
                    $obj->GetId()
                    )
                )
                as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }


     public function getAllResponsiblesContractedAt(C3op_Projects_Project $obj) {
        $result = array();

        foreach ($this->db->query(sprintf('SELECT r.id
            FROM projects_actions a
            INNER JOIN resources_responsibles r ON a.id = r.action
            WHERE (r.contact > 0 OR r.institution > 0)
            AND a.project = %d
            AND (
            a.status = %d
            OR a.status = %d
            )'
            , $obj->getId()
            , C3op_Resources_ResponsibleStatusConstants::STATUS_CONTRACTED
            , C3op_Resources_ResponsibleStatusConstants::STATUS_ACQUITTED

                )) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function getAllResponsiblesContractedOrPredictedAt(C3op_Projects_Project $obj) {
        $result = array();

        foreach ($this->db->query(sprintf('SELECT r.id
            FROM projects_actions a
            INNER JOIN resources_responsibles r ON a.id = r.action
            WHERE a.project = %d
            AND (
            r.status = %d
            OR r.status = %d
            OR r.status = %d
            OR r.status = %d
            )'
            , $obj->getId()
            , C3op_Resources_ResponsibleStatusConstants::STATUS_UNDEFINED
            , C3op_Resources_ResponsibleStatusConstants::STATUS_CONTRACTED
            , C3op_Resources_ResponsibleStatusConstants::STATUS_ACQUITTED
            , C3op_Resources_ResponsibleStatusConstants::STATUS_FORESEEN

                )) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function getAllUniqueResponsiblesContractedAt(C3op_Projects_Project $obj) {
        $result = array();


        foreach ($this->db->query(sprintf('SELECT r.id
            FROM projects_actions a
            INNER JOIN resources_responsibles t ON a.id = r.action
            WHERE a.project = %d
            AND r.linkage > 0
            AND (
            r.status = %d
            OR r.status = %d
            OR r.status = %d
            ) GROUP BY CONCAT(r.institution, \'|\', r.contact'
            , $obj->getId()
            , C3op_Resources_ResponsibleStatusConstants::STATUS_UNDEFINED
            , C3op_Resources_ResponsibleStatusConstants::STATUS_CONTRACTED
            , C3op_Resources_ResponsibleStatusConstants::STATUS_ACQUITTED

                )) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function getAllUniqueResponsiblesContractedOrPredictedAt(C3op_Projects_Project $obj) {
        $result = array();


        foreach ($this->db->query(sprintf('SELECT r.id
            FROM projects_actions a
            INNER JOIN resources_responsibles t ON a.id = r.action
            WHERE a.project = %d
            AND r.linkage > 0
            AND (
            r.status = %d
            OR r.status = %d
            OR r.status = %d
            ) GROUP BY CONCAT(r.institution, \'|\', r.contact'
            , $obj->getId()
            , C3op_Resources_ResponsibleStatusConstants::STATUS_UNDEFINED
            , C3op_Resources_ResponsibleStatusConstants::STATUS_CONTRACTED
            , C3op_Resources_ResponsibleStatusConstants::STATUS_ACQUITTED
            , C3op_Resources_ResponsibleStatusConstants::STATUS_FORESEEN

                )) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function findMainPositionForAPerson(C3op_Resources_Responsible $obj) {
        foreach ($this->db->query(sprintf('SELECT r.id
            FROM resources_responsibles r
            INNER JOIN register_contacts t ON r.contact = t.id
            INNER JOIN register_linkages l ON t.linkage = l.id
            WHERE r.linkage = %d AND r.project = %d
            AND (
            r.status = %d
            OR r.status = %d
            OR r.status = %d
            ) ORDER BY value DESC LIMIT 1'
            , $obj->getLinkage()
            , $obj->getProject()
            , C3op_Resources_ResponsibleStatusConstants::STATUS_CONTRACTED
            , C3op_Resources_ResponsibleStatusConstants::STATUS_ACQUITTED
            , C3op_Resources_ResponsibleStatusConstants::STATUS_FORESEEN

                )) as $row) {

            return $this->findById($row['id']);
        }
        return $obj;

    }

    public function getNextOutlayToPayTo(C3op_Resources_Responsible $obj)
    {
        $query = $this->db->prepare('SELECT id FROM finances_outlays WHERE responsible = :id AND predicted_value > 0 AND (real_value IS NULL OR real_value = 0.0) ORDER BY predicted_date LIMIT 1;');
        $query->bindValue(':id', $obj->GetId(), PDO::PARAM_STR);
        $query->execute();

        if ($query->rowCount() > 0) {
                $result = $query->fetch();
                return $result;
        } else {
            return null;
        }
    }

   public function getSumOfPayedOutlays(C3op_Resources_Responsible $obj)
    {
        $query = $this->db->prepare('SELECT SUM(real_value) as sum FROM finances_outlays WHERE responsible = :id AND real_value > 0 AND real_date IS NOT NULL;');
        $query->bindValue(':id', $obj->GetId(), PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();
        if ( is_null($result['sum'])) {
            return 0;
        } else {
            return $result['sum'];
        }
    }

    public function getSumOfProvidedOutlays(C3op_Resources_Responsible $obj)
    {
        $query = $this->db->prepare('SELECT SUM(predicted_value) as sum FROM finances_outlays WHERE responsible =  :id AND predicted_value > 0;');
        $query->bindValue(':id', $obj->GetId(), PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();
        if ( is_null($result['sum'])) {
            return 0;
        } else {
            return $result['sum'];
        }
    }

    public function getSumOfProvidedButNotPayedOutlays(C3op_Resources_Responsible $obj)
    {
        $query = $this->db->prepare('SELECT SUM(predicted_value) as sum FROM finances_outlays WHERE responsible =  :id AND predicted_value > 0 AND (real_value IS NULL OR real_value = 0.0);');
        $query->bindValue(':id', $obj->GetId(), PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();
        if ( is_null($result['sum'])) {
            return 0;
        } else {
            return $result['sum'];
        }
    }


}