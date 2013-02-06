<?php

class C3op_Resources_TeamMemberMapper {

    protected $db;
    protected $identityMap;

    function __construct() {
        $this->db = Zend_Registry::get('db');
        $this->identityMap = new SplObjectStorage;
    }

    public function getAllIds() {
        $result = array();
            foreach ($this->db->query('SELECT id FROM resources_team_members;') as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function insert(C3op_Resources_TeamMember $new) {


        $query = $this->db->prepare("INSERT INTO resources_team_members (project, action, linkage, description, value, status) VALUES (:project, :action, :linkage, :description, :value, :status)");

        $query->bindValue(':project', $new->GetProject(), PDO::PARAM_INT);
        $query->bindValue(':action', $new->GetAction(), PDO::PARAM_INT);
        $query->bindValue(':linkage', $new->getLinkage(), PDO::PARAM_INT);
        $query->bindValue(':description', $new->GetDescription(), PDO::PARAM_STR);
        $query->bindValue(':value', $new->GetValue(), PDO::PARAM_STR);
        $query->bindValue(':status', $new->GetStatus(), PDO::PARAM_INT);

        $query->execute();

        $new->SetId((int)$this->db->lastInsertId());
        $this->identityMap[$new] = $new->GetId();

    }

    public function update(C3op_Resources_TeamMember $obj) {
        if (!isset($this->identityMap[$obj])) {
            throw new C3op_Resources_TeamMemberMapperException('Object has no ID, cannot update.');
        }

        $query = $this->db->prepare("UPDATE resources_team_members SET project = :project, action = :action, description = :description, linkage = :linkage, value = :value, status = :status WHERE id = :id;");

        $query->bindValue(':project', $obj->GetProject(), PDO::PARAM_STR);
        $query->bindValue(':action', $obj->GetAction(), PDO::PARAM_STR);
        $query->bindValue(':description', $obj->GetDescription(), PDO::PARAM_STR);
        $query->bindValue(':linkage', $obj->getLinkage(), PDO::PARAM_STR);
        $query->bindValue(':value', $obj->GetValue(), PDO::PARAM_STR);
        $query->bindValue(':status', $obj->GetStatus(), PDO::PARAM_STR);
        $query->bindValue(':id', $this->identityMap[$obj], PDO::PARAM_STR);

        try {
            $query->execute();
        } catch (Exception $e) {
            throw new C3op_Resources_TeamMemberException("$sql failed");
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

        $query = $this->db->prepare('SELECT project, action, description, linkage, value, status FROM resources_team_members WHERE id = :id;');
        $query->bindValue(':id', $id, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();

        if (empty($result)) {
            throw new C3op_Resources_TeamMemberMapperException(sprintf('There is no Human Resource with id #%d.', $id));
        }
        $obj = new C3op_Resources_TeamMember();

        $this->setAttributeValue($obj, $id, 'id');


        $this->setAttributeValue($obj, $result['project'], 'project');
        $this->setAttributeValue($obj, $result['action'], 'action');
        $this->setAttributeValue($obj, $result['description'], 'description');
        $this->setAttributeValue($obj, $result['linkage'], 'linkage');
        $this->setAttributeValue($obj, $result['value'], 'value');
        $this->setAttributeValue($obj, $result['status'], 'status');

        $this->identityMap[$obj] = $id;

        return $obj;

        $this->FetchDates($obj);
    }

    public function delete(C3op_Resources_TeamMember $i) {
        if (!isset($this->identityMap[$i])) {
            throw new C3op_Resources_TeamMemberMapperException('Object has no ID, cannot delete.');
        }
        $this->db->exec(
            sprintf(
                'DELETE FROM resources_team_members WHERE id = %d;',
                $this->identityMap[$i]
            )
        );
        unset($this->identityMap[$i]);
    }

     public function getAllTeamMembersOnAction(C3op_Projects_Action $a) {
        $result = array();
            foreach ($this->db->query(
                    sprintf('SELECT id FROM resources_team_members WHERE action = %d;', $a->GetId())) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

   private function setAttributeValue(C3op_Resources_TeamMember $i, $fieldValue, $attributeName)
    {
        $attribute = new ReflectionProperty($i, $attributeName);
        $attribute->setAccessible(TRUE);
        $attribute->setValue($i, $fieldValue);
    }

    public function getAllActionsEngaging(C3op_Register_Linkage $obj, C3op_Projects_Project $project)
    {
        $query = $this->db->prepare('SELECT a.id as id, t.id as teamMemberId
                    FROM projects_actions a
                    LEFT JOIN resources_team_members t ON a.id = t.action
                    LEFT JOIN register_linkages l ON t.linkage = l.id
                    WHERE a.project = :project AND l.id = :linkage
                    AND (t.status = :foreseen OR t.status = :contracted OR t.status = :acquitted);');
        $query->bindValue(':project', $project->GetId(), PDO::PARAM_STR);
        $query->bindValue(':linkage', $obj->GetId(), PDO::PARAM_STR);
        $query->bindValue(':foreseen', C3op_Resources_TeamMemberStatusConstants::STATUS_FORESEEN, PDO::PARAM_STR);
        $query->bindValue(':contracted', C3op_Resources_TeamMemberStatusConstants::STATUS_CONTRACTED, PDO::PARAM_STR);
        $query->bindValue(':acquitted', C3op_Resources_TeamMemberStatusConstants::STATUS_ACQUITTED, PDO::PARAM_STR);
        $query->execute();
        $resultPDO = $query->fetchAll();

        $result = array();
        foreach ($resultPDO as $row) {
            $result[$row['id']] = array(
                'teamMember' => $row['teamMemberId'],
            );
        }
        return $result;
    }

    public function getAllOutlays(C3op_Resources_TeamMember $obj)
    {
        $result = array();
        foreach ($this->db->query(
                sprintf(
                    'SELECT id FROM finances_outlays WHERE team_member = %d;',
                    $obj->GetId()
                    )
                )
                as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

     public function getAllTeamMembersContractedAt(C3op_Projects_Project $obj) {
        $result = array();

        foreach ($this->db->query(sprintf('SELECT t.id
            FROM projects_actions a
            INNER JOIN resources_team_members t ON a.id = t.action
            WHERE t.linkage > 0
            AND a.project = %d
            AND (
            a.status = %d
            OR a.status = %d
            )'
            , $obj->getId()
            , C3op_Resources_TeamMemberStatusConstants::STATUS_CONTRACTED
            , C3op_Resources_TeamMemberStatusConstants::STATUS_ACQUITTED

                )) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

     public function getAllUnassignedPositionsAt(C3op_Projects_Project $obj) {
        $result = array();

        foreach ($this->db->query(sprintf('SELECT t.id
            FROM projects_actions a
            INNER JOIN resources_team_members t ON a.id = t.action
            WHERE t.linkage = 0
            AND a.project = %d
            AND (
            a.status = %d
            OR a.status = %d
            )'
            , $obj->getId()
            , C3op_Resources_TeamMemberStatusConstants::STATUS_UNDEFINED
            , C3op_Resources_TeamMemberStatusConstants::STATUS_FORESEEN

                )) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function getAllTeamMembersContractedOrPredictedAt(C3op_Projects_Project $obj) {
        $result = array();

        foreach ($this->db->query(sprintf('SELECT t.id
            FROM projects_actions a
            INNER JOIN resources_team_members t ON a.id = t.action
            WHERE a.project = %d
            AND (
            t.status = %d
            OR t.status = %d
            OR t.status = %d
            OR t.status = %d
            )'
            , $obj->getId()
            , C3op_Resources_TeamMemberStatusConstants::STATUS_UNDEFINED
            , C3op_Resources_TeamMemberStatusConstants::STATUS_CONTRACTED
            , C3op_Resources_TeamMemberStatusConstants::STATUS_ACQUITTED
            , C3op_Resources_TeamMemberStatusConstants::STATUS_FORESEEN

                )) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function getAllUniqueTeamMembersContractedAt(C3op_Projects_Project $obj) {
        $result = array();


//SELECT _t.linkage, resources_team_members.id, _t.value
//
//FROM  (
//SELECT  `linkage` , MAX(  `value` )  `value`
//FROM resources_team_members
//WHERE linkage >0
//GROUP BY linkage
//ORDER BY MAX( value ) DESC
//) _t JOIN resources_team_members
//USING (linkage, value) INNER JOIN projects_actions a ON resources_team_members.action = a.id WHERE a.project = 4
//ORDER BY _t.value DESC


        foreach ($this->db->query(sprintf('SELECT t.id
            FROM projects_actions a
            INNER JOIN resources_team_members t ON a.id = t.action
            WHERE a.project = %d
            AND t.linkage > 0
            AND (
            t.status = %d
            OR t.status = %d
            OR t.status = %d
            ) GROUP BY t.linkage'
            , $obj->getId()
            , C3op_Resources_TeamMemberStatusConstants::STATUS_UNDEFINED
            , C3op_Resources_TeamMemberStatusConstants::STATUS_CONTRACTED
            , C3op_Resources_TeamMemberStatusConstants::STATUS_ACQUITTED

                )) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function getAllUniqueTeamMembersContractedOrPredictedAt(C3op_Projects_Project $obj) {
        $result = array();

        foreach ($this->db->query(sprintf('SELECT t.id
            FROM projects_actions a
            INNER JOIN resources_team_members t ON a.id = t.action
            WHERE a.project = %d
            AND t.linkage > 0
            AND (
            t.status = %d
            OR t.status = %d
            OR t.status = %d
            OR t.status = %d
            ) GROUP BY t.linkage'
            , $obj->getId()
            , C3op_Resources_TeamMemberStatusConstants::STATUS_UNDEFINED
            , C3op_Resources_TeamMemberStatusConstants::STATUS_CONTRACTED
            , C3op_Resources_TeamMemberStatusConstants::STATUS_ACQUITTED
            , C3op_Resources_TeamMemberStatusConstants::STATUS_FORESEEN

                )) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function findMainPositionForAPerson(C3op_Resources_TeamMember $obj) {
        foreach ($this->db->query(sprintf('SELECT t.id
            FROM resources_team_members t
            INNER JOIN register_linkages l ON t.linkage = l.id
            WHERE t.linkage = %d AND t.project = %d
            AND (
            t.status = %d
            OR t.status = %d
            OR t.status = %d
            ) ORDER BY value DESC LIMIT 1'
            , $obj->getLinkage()
            , $obj->getProject()
            , C3op_Resources_TeamMemberStatusConstants::STATUS_CONTRACTED
            , C3op_Resources_TeamMemberStatusConstants::STATUS_ACQUITTED
            , C3op_Resources_TeamMemberStatusConstants::STATUS_FORESEEN

                )) as $row) {

            return $this->findById($row['id']);
        }
        return $obj;

    }

    public function getNextOutlayToPayTo(C3op_Resources_TeamMember $obj)
    {
        $query = $this->db->prepare('SELECT id FROM finances_outlays WHERE team_member = :id AND predicted_value > 0 AND (real_value IS NULL OR real_value = 0.0) ORDER BY predicted_date LIMIT 1;');
        $query->bindValue(':id', $obj->GetId(), PDO::PARAM_STR);
        $query->execute();

        if ($query->rowCount() > 0) {
                $result = $query->fetch();
                return $result;
        } else {
            return null;
        }
    }

   public function getSumOfPayedOutlays(C3op_Resources_TeamMember $obj)
    {
//       die(sprintf('SELECT SUM(real_value) as sum FROM finances_outlays WHERE team_member = \'%s\' AND real_value > 0 AND real_date IS NOT NULL AND real_date <> "0000-00-00";', $obj->GetId()));
        $query = $this->db->prepare('SELECT SUM(real_value) as sum FROM finances_outlays WHERE team_member = :id AND real_value > 0 AND real_date IS NOT NULL AND real_date <> "0000-00-00";');
        $query->bindValue(':id', $obj->GetId(), PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();
        if ( is_null($result['sum'])) {
            return 0;
        } else {
            return $result['sum'];
        }
    }

    public function getSumOfProvidedOutlays(C3op_Resources_TeamMember $obj)
    {
        $query = $this->db->prepare('SELECT SUM(predicted_value) as sum FROM finances_outlays WHERE team_member = :id AND predicted_value > 0;');
        $query->bindValue(':id', $obj->GetId(), PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();
        if ( is_null($result['sum'])) {
            return 0;
        } else {
            return $result['sum'];
        }
    }

    public function getSumOfProvidedButNotPayedOutlays(C3op_Resources_TeamMember $obj)
    {
        $query = $this->db->prepare('SELECT SUM(predicted_value) as sum FROM finances_outlays WHERE team_member = :id AND predicted_value > 0 AND (real_value IS NULL OR real_value = 0.0);');
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