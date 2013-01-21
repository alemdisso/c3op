<?php

class C3op_Resources_OutsideServiceMapper {

    protected $db;
    protected $identityMap;

    function __construct() {
        $this->db = Zend_Registry::get('db');
        $this->identityMap = new SplObjectStorage;
    }

    public function getAllIds() {
        $result = array();
            foreach ($this->db->query('SELECT id FROM resources_outside_services;') as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function insert(C3op_Resources_OutsideService $new) {


        $query = $this->db->prepare("INSERT INTO resources_outside_services (action, institution, linkage, description, value, status) VALUES (:action, :institution, :linkage, :description, :value, :status)");

        $query->bindValue(':action', $new->GetAction(), PDO::PARAM_INT);
        $query->bindValue(':institution', $new->getInstitution(), PDO::PARAM_INT);
        $query->bindValue(':linkage', $new->getLinkage(), PDO::PARAM_INT);
        $query->bindValue(':description', $new->GetDescription(), PDO::PARAM_STR);
        $query->bindValue(':value', $new->GetValue(), PDO::PARAM_STR);
        $query->bindValue(':status', $new->GetStatus(), PDO::PARAM_INT);

        $query->execute();

        $new->SetId((int)$this->db->lastInsertId());
        $this->identityMap[$new] = $new->GetId();

    }

    public function update(C3op_Resources_OutsideService $obj) {
        if (!isset($this->identityMap[$obj])) {
            throw new C3op_Resources_OutsideServiceMapperException('Object has no ID, cannot update.');
        }

        $query = $this->db->prepare("UPDATE resources_outside_services SET action = :action, description = :description, institution = :institution, linkage = :linkage, value = :value, status = :status WHERE id = :id;");

        $query->bindValue(':action', $obj->GetAction(), PDO::PARAM_STR);
        $query->bindValue(':description', $obj->GetDescription(), PDO::PARAM_STR);
        $query->bindValue(':institution', $obj->getInstitution(), PDO::PARAM_STR);
        $query->bindValue(':linkage', $obj->getLinkage(), PDO::PARAM_STR);
        $query->bindValue(':value', $obj->GetValue(), PDO::PARAM_STR);
        $query->bindValue(':status', $obj->GetStatus(), PDO::PARAM_STR);
        $query->bindValue(':id', $this->identityMap[$obj], PDO::PARAM_STR);

        try {
            $query->execute();
        } catch (Exception $e) {
            throw new C3op_Resources_OutsideServiceException("$sql failed");
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

        $query = $this->db->prepare('SELECT action, description, institution, linkage, value, status FROM resources_outside_services WHERE id = :id;');
        $query->bindValue(':id', $id, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();

        if (empty($result)) {
            throw new C3op_Resources_OutsideServiceMapperException(sprintf('There is no Human Resource with id #%d.', $id));
        }
        $obj = new C3op_Resources_OutsideService();

        $this->setAttributeValue($obj, $id, 'id');
        $this->setAttributeValue($obj, $result['action'], 'action');
        $this->setAttributeValue($obj, $result['description'], 'description');
        $this->setAttributeValue($obj, $result['institution'], 'institution');
        $this->setAttributeValue($obj, $result['linkage'], 'linkage');
        $this->setAttributeValue($obj, $result['value'], 'value');
        $this->setAttributeValue($obj, $result['status'], 'status');

        $this->identityMap[$obj] = $id;
        return $obj;

        $this->FetchDates($obj);
    }

    public function delete(C3op_Resources_OutsideService $i) {
        if (!isset($this->identityMap[$i])) {
            throw new C3op_Resources_OutsideServiceMapperException('Object has no ID, cannot delete.');
        }
        $this->db->exec(
            sprintf(
                'DELETE FROM resources_outside_services WHERE id = %d;',
                $this->identityMap[$i]
            )
        );
        unset($this->identityMap[$i]);
    }

     public function getAllOutsideServicesOnAction(C3op_Projects_Action $a) {
        $result = array();
            foreach ($this->db->query(
                    sprintf('SELECT id FROM resources_outside_services WHERE action = %d;', $a->GetId())) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

   private function setAttributeValue(C3op_Resources_OutsideService $i, $fieldValue, $attributeName)
    {
        $attribute = new ReflectionProperty($i, $attributeName);
        $attribute->setAccessible(TRUE);
        $attribute->setValue($i, $fieldValue);
    }

    public function getAllOutsideServicesContractedOrPredictedAt(C3op_Projects_Project $obj) {
        $result = array();

        foreach ($this->db->query(sprintf('SELECT s.id
            FROM projects_actions a
            INNER JOIN resources_outside_services s ON a.id = s.action
            WHERE a.project = %d
            AND (
            s.status = %d
            OR s.status = %d
            OR s.status = %d
            OR s.status = %d
            )'
            , $obj->getId()
            , C3op_Resources_OutsideServiceStatusConstants::STATUS_UNDEFINED
            , C3op_Resources_OutsideServiceStatusConstants::STATUS_CONTRACTED
            , C3op_Resources_OutsideServiceStatusConstants::STATUS_ACQUITTED
            , C3op_Resources_OutsideServiceStatusConstants::STATUS_FORESEEN
                )) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function getAllPaylees(C3op_Resources_OutsideService $obj)
    {
        $result = array();
        foreach ($this->db->query(
                sprintf(
                    'SELECT id FROM projects_paylees WHERE outside_service = %d;',
                    $obj->GetId()
                    )
                )
                as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function getSumOfPayedOutlays(C3op_Resources_OutsideService $obj)
    {
        $query = $this->db->prepare('SELECT SUM(real_value) as sum FROM finances_outlays WHERE outside_service = :id AND real_value > 0 AND real_date IS NOT NULL AND real_date <> "0000-00-00";');
        $query->bindValue(':id', $obj->GetId(), PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();
        return $result['sum'];
    }

    public function getSumOfProvidedButNotPayedOutlays(C3op_Resources_OutsideService $obj)
    {
        $query = $this->db->prepare('SELECT SUM(predicted_value) as sum FROM finances_outlays WHERE outside_service = :id AND predicted_value > 0 AND (real_value IS NULL OR real_value = 0.0);');
        $query->bindValue(':id', $obj->GetId(), PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();
        return $result['sum'];
    }



}