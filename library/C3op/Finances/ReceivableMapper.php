<?php

class C3op_Finances_ReceivableMapper
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
        $query = $this->db->prepare('SELECT id FROM finances_receivables WHERE 1=1;');
        $query->execute();
        $resultPDO = $query->fetchAll();

        $result = array();
        foreach ($resultPDO as $row) {
            $result[] = $row['id'];
        }
        return $result;

    }

    public function insert(C3op_Finances_Receivable $obj)
    {

        $query = $this->db->prepare("INSERT INTO finances_receivables (project, title, description, predicted_date, real_date, predicted_value, real_value)
            VALUES (:project, :title, :description, :predicted_date, :real_date, :predicted_value, :real_value)");

        $query->bindValue(':project', $obj->GetProject(), PDO::PARAM_STR);
        $query->bindValue(':title', $obj->GetTitle(), PDO::PARAM_STR);
        $query->bindValue(':description', $obj->GetDescription(), PDO::PARAM_STR);
        $query->bindValue(':predicted_date', $obj->GetPredictedDate(), PDO::PARAM_STR);
        $query->bindValue(':real_date', $obj->GetRealDate(), PDO::PARAM_STR);
        $query->bindValue(':predicted_value', $obj->GetPredictedValue(), PDO::PARAM_STR);
        $query->bindValue(':real_value', $obj->GetRealValue(), PDO::PARAM_STR);

        $query->execute();

        $obj->SetId((int)$this->db->lastInsertId());
        $this->identityMap[$obj] = $obj->GetId();

    }

    public function update(C3op_Finances_Receivable $obj)
    {
        if (!isset($this->identityMap[$obj])) {
            throw new C3op_Finances_ReceivableMapperException('Object has no ID, cannot update.');
        }

        $query = $this->db->prepare("UPDATE finances_receivables SET project = :project, title = :title, description = :description, predicted_date = :predicted_date, real_date = :real_date, predicted_value = :predicted_value, real_value = :real_value WHERE id = :id;");

        $query->bindValue(':project', $obj->GetProject(), PDO::PARAM_STR);
        $query->bindValue(':title', $obj->GetTitle(), PDO::PARAM_STR);
        $query->bindValue(':description', $obj->GetDescription(), PDO::PARAM_STR);
        $query->bindValue(':predicted_date', $obj->GetPredictedDate(), PDO::PARAM_STR);
        $query->bindValue(':real_date', $obj->GetRealDate(), PDO::PARAM_STR);
        $query->bindValue(':predicted_value', $obj->GetPredictedValue(), PDO::PARAM_STR);
        $query->bindValue(':real_value', $obj->GetRealValue(), PDO::PARAM_STR);
        $query->bindValue(':id', $this->identityMap[$obj], PDO::PARAM_STR);

        try {
            $query->execute();
        } catch (Exception $e) {
            throw new C3op_Finances_ReceivableException("sql failed");
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

        $query = $this->db->prepare('SELECT project, title, description, predicted_date, real_date, predicted_value, real_value FROM finances_receivables WHERE id = :id;');
        $query->bindValue(':id', $id, PDO::PARAM_STR);
        $query->execute();

        $result = $query->fetch();

        if (empty($result)) {
            throw new C3op_Finances_ReceivableMapperException(sprintf('There is no receivable with id #%d.', $id));
        }
        $project = $result['project'];

        $obj = new C3op_Finances_Receivable($result['project'], $result['predicted_date'], $result['predicted_value'], $id);
        $this->setAttributeValue($obj, $id, 'id');
        $this->setAttributeValue($obj, $result['title'], 'title');
        $this->setAttributeValue($obj, $result['description'], 'description');
        $this->setAttributeValue($obj, $result['real_date'], 'realDate');
        $this->setAttributeValue($obj, $result['real_value'], 'realValue');

        $deliveryData = $this->fetchFirstDeliveryDate($obj);
        $this->setAttributeValue($obj, $deliveryData, 'deliveryDate');

        $this->identityMap[$obj] = $id;

        return $obj;

    }

    public function delete(C3op_Finances_Receivable $obj)
    {
        if (!isset($this->identityMap[$obj])) {
            throw new C3op_Finances_ReceivableMapperException('Object has no ID, cannot delete.');
        }
        $query = $this->db->prepare('DELETE FROM finances_receivables WHERE id = :id;');
        $query->bindValue(':id', $this->identityMap[$obj], PDO::PARAM_STR);
        $query->execute();
        unset($this->identityMap[$obj]);
    }


    private function setAttributeValue(C3op_Finances_Receivable $a, $fieldValue, $attributeName)
    {
        $attribute = new ReflectionProperty($a, $attributeName);
        $attribute->setAccessible(TRUE);
        $attribute->setValue($a, $fieldValue);
    }

    public function getAllProducts(C3op_Finances_Receivable $obj)
    {

       $query = $this->db->prepare('SELECT id FROM projects_actions WHERE requirement_for_receiving = :requirement_for_receiving;');
        $query->bindValue(':requirement_for_receiving', $obj->GetId(), PDO::PARAM_STR);
        $query->execute();
        $resultPDO = $query->fetchAll();

        $result = array();
        foreach ($resultPDO as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function updateDeliveries(C3op_Finances_Receivable $obj, $date)
    {
        $firstDelivery = $this->fetchFirstDeliveryDate($obj);

        if ($firstDelivery === null) {
            $query = $this->db->prepare('INSERT INTO projects_deliveries (predicted_date, receivable) VALUES (:predicted_date, :receivable)');
            $query->bindValue(':predicted_date', $date, PDO::PARAM_STR);
            $query->bindValue(':receivable', $obj->GetId(), PDO::PARAM_STR);


        } else {
            $query = $this->db->prepare('UPDATE projects_deliveries SET predicted_date = :predicted_date WHERE receivable = :receivable');
            $query->bindValue(':predicted_date', $date, PDO::PARAM_STR);
            $query->bindValue(':receivable', $obj->GetId(), PDO::PARAM_STR);
        }

        try {
            $query->execute();
        } catch (Exception $e) {
            throw new C3op_Finances_ReceivableException("sql failed");
        }
    }

    private function fetchFirstDeliveryDate(C3op_Finances_Receivable $obj)
    {

        $query = $this->db->prepare('SELECT predicted_date FROM projects_deliveries WHERE receivable = :receivable AND (real_date IS NULL) ORDER BY predicted_date LIMIT 1;');
        $query->bindValue(':receivable', $obj->GetId(), PDO::PARAM_STR);
        $query->execute();
        $resultPDO = $query->fetchAll();
        $result = array();
        foreach ($resultPDO as $row) {
            $result[] = $row['predicted_date'];
            return $result[0];
        }

        return null;


    }

    public function getAllReceivables(C3op_Projects_Project $obj)
    {
        $query = $this->db->prepare('SELECT id FROM finances_receivables WHERE project = :project;');
        $query->bindValue(':project', $obj->getId(), PDO::PARAM_STR);
        $query->execute();
        $resultPDO = $query->fetchAll();

        $result = array();
        foreach ($resultPDO as $row) {
            $result[] = $row['id'];
        }
        return $result;

    }



}