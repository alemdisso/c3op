<?php

class C3op_Projects_DeliveryMapper
{

    protected $db;
    protected $identityMap;

    function __construct()
    {
        $this->db = Zend_Registry::get('db');
        $this->identityMap = new SplObjectStorage;
    }

    public function getAllIds()
    {
        $query = $this->db->prepare('SELECT id FROM projects_deliveries WHERE 1=1;');
        $query->execute();
        $resultPDO = $query->fetchAll();

        $result = array();
        foreach ($resultPDO as $row) {
            $result[] = $row['id'];
        }
        return $result;

    }

    public function insert(C3op_Projects_Delivery $obj)
    {

        $query = $this->db->prepare("INSERT INTO projects_deliveries (project, receivable, predicted_date, real_date)
            VALUES (:project, :receivable, :predicted_date, :real_date)");

        $query->bindValue(':project', $obj->GetProject(), PDO::PARAM_STR);
        $query->bindValue(':receivable', $obj->GetReceivable(), PDO::PARAM_STR);
        $query->bindValue(':predicted_date', $obj->GetPredictedDate(), PDO::PARAM_STR);
        $query->bindValue(':real_date', $obj->GetRealDate(), PDO::PARAM_STR);

        $query->execute();

        $obj->SetId((int)$this->db->lastInsertId());
        $this->identityMap[$obj] = $obj->GetId();

    }

    public function update(C3op_Projects_Delivery $obj)
    {
        if (!isset($this->identityMap[$obj])) {
            throw new C3op_Projects_DeliveryMapperException('Object has no ID, cannot update.');
        }

        $query = $this->db->prepare("UPDATE projects_deliveries SET project = :project, receivable = :receivable, predicted_date = :predicted_date, real_date = :real_date WHERE id = :id;");

        $query->bindValue(':project', $obj->GetProject(), PDO::PARAM_STR);
        $query->bindValue(':receivable', $obj->GetReceivable(), PDO::PARAM_STR);
        $query->bindValue(':predicted_date', $obj->GetPredictedDate(), PDO::PARAM_STR);
        $query->bindValue(':real_date', $obj->GetRealDate(), PDO::PARAM_STR);
        $query->bindValue(':id', $this->identityMap[$obj], PDO::PARAM_STR);

        try {
            $query->execute();
        } catch (Exception $e) {
            throw new C3op_Projects_ActionException("sql failed");
        }

    }

    public function findById($id)
    {
        $this->identityMap->rewind();
        while ($this->identityMap->valid()) {
            if ($this->identityMap->getInfo() == $id) {
                return $this->identityMap->current();
            }
            $this->identityMap->next();
        }

        $query = $this->db->prepare('SELECT project, receivable, predicted_date, real_date FROM projects_deliveries WHERE id = :id;');
        $query->bindValue(':id', $id, PDO::PARAM_STR);
        $query->execute();

        $result = $query->fetch();

        if (empty($result)) {
            throw new C3op_Projects_DeliveryMapperException(sprintf('There is no delivery with id #%d.', $id));
        }
        $project = $result['project'];

        $obj = new C3op_Projects_Delivery($result['receivable'], $result['project'], $result['predicted_date'], $id);
        $this->setAttributeValue($obj, $id, 'id');
        //$this->setAttributeValue($obj, $result['project'], 'project');
        $this->setAttributeValue($obj, $result['real_date'], 'realDate');

        $receivableValue = $this->fetchReceivablePredictedValue($obj);
        $this->setAttributeValue($obj, $receivableValue, 'receivablePredictedValue');


        $this->identityMap[$obj] = $id;

        return $obj;

    }

    public function findByReceivableId($receivableId)
    {

        $query = $this->db->prepare('SELECT id FROM projects_deliveries WHERE receivable = :receivable;');
        $query->bindValue(':receivable', $receivableId, PDO::PARAM_STR);
        $query->execute();

        $result = $query->fetch();

        if (empty($result)) {
            throw new C3op_Projects_DeliveryMapperException(sprintf('There is no delivery for receivable #%d.', $receivableId));
        }
        $id = $result['id'];

        $obj = $this->findById($id);
        return $obj;

    }

    public function delete(C3op_Projects_Delivery $obj)
    {
        if (!isset($this->identityMap[$obj])) {
            throw new C3op_Projects_DeliveryMapperException('Object has no ID, cannot delete.');
        }
        $query = $this->db->prepare('DELETE FROM projects_deliveries WHERE id = :id;');
        $query->bindValue(':id', $this->identityMap[$obj], PDO::PARAM_STR);
        $query->execute();
        unset($this->identityMap[$obj]);
    }


    private function setAttributeValue(C3op_Projects_Delivery $a, $fieldValue, $attributeName)
    {
        $attribute = new ReflectionProperty($a, $attributeName);
        $attribute->setAccessible(TRUE);
        $attribute->setValue($a, $fieldValue);
    }

    public function getAllProducts(C3op_Projects_Delivery $obj)
    {

        $query = $this->db->prepare('SELECT id FROM projects_actions WHERE requirement_for_receiving = :requirement_for_receiving;');
        $query->bindValue(':requirement_for_receiving', $obj->GetReceivable(), PDO::PARAM_STR);
        $query->execute();

        $resultPDO = $query->fetchAll();
        $result = array();
        foreach ($resultPDO as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function findNextDeliveryAtProject($projectId)
    {

        $query = $this->db->prepare('SELECT d.id FROM projects_deliveries d
            INNER JOIN finances_receivables r ON d.receivable = r.id
            WHERE d.project = :project
                    AND r.real_date IS NULL ORDER BY d.predicted_date LIMIT 1;');
        $query->bindValue(':project', $projectId, PDO::PARAM_STR);
        $query->execute();

        $result = $query->fetch();

        if (empty($result)) {
            return null;
        }
        $id = $result['id'];

        $obj = $this->findById($id);

        return $obj;

    }



    private function fetchReceivablePredictedValue(C3op_Projects_Delivery $obj)
    {

        $query = $this->db->prepare('SELECT predicted_value FROM finances_receivables WHERE id = :receivable AND (real_date IS NULL) ORDER BY predicted_date LIMIT 1;');
        $query->bindValue(':receivable', $obj->GetReceivable(), PDO::PARAM_STR);
        $query->execute();
        $resultPDO = $query->fetchAll();
        $result = array();
        foreach ($resultPDO as $row) {
            $result[] = $row['predicted_value'];
            return $result[0];
        }

        return null;


    }



}