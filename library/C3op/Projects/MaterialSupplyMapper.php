<?php

class C3op_Projects_MaterialSupplyMapper {

    protected $db;
    protected $identityMap;

    function __construct() {
        $this->db = Zend_Registry::get('db');
        $this->identityMap = new SplObjectStorage;
    }

    public function getAllIds() {
        $result = array();
            foreach ($this->db->query('SELECT id FROM projects_material_supplies;') as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function insert(C3op_Projects_MaterialSupply $new) {


        $query = $this->db->prepare("INSERT INTO projects_material_supplies (
            action, description, institution, linkage, quantity, unit, unit_value, total_value, status) VALUES (:action, :description, :institution, :linkage, :quantity, :unit, :unitValue, :totalValue, :status)");

        $query->bindValue(':action', $new->GetAction(), PDO::PARAM_INT);
        $query->bindValue(':description', $new->GetDescription(), PDO::PARAM_STR);
        $query->bindValue(':institution', $new->getInstitution(), PDO::PARAM_INT);
        $query->bindValue(':linkage', $new->getLinkage(), PDO::PARAM_INT);
        $query->bindValue(':quantity', $new->getQuantity(), PDO::PARAM_STR);
        $query->bindValue(':unit', $new->GetUnit(), PDO::PARAM_INT);
        $query->bindValue(':unitValue', $new->getUnitValue(), PDO::PARAM_INT);
        $query->bindValue(':totalValue', $new->GetTotalValue(), PDO::PARAM_STR);
        $query->bindValue(':status', $new->GetStatus(), PDO::PARAM_INT);

        $query->execute();

        $new->SetId((int)$this->db->lastInsertId());
        $this->identityMap[$new] = $new->GetId();

    }

    public function update(C3op_Projects_MaterialSupply $obj) {
        if (!isset($this->identityMap[$obj])) {
            throw new C3op_Projects_MaterialSupplyMapperException('Object has no ID, cannot update.');
        }

        $query = $this->db->prepare("UPDATE projects_material_supplies SET action = :action, description = :description, institution = :institution, linkage = :linkage, quantity = :quantity, unit = :unit, unit_value = :unitValue, total_value = :totalValue, status = :status WHERE id = :id;");

        $query->bindValue(':action', $obj->GetAction(), PDO::PARAM_STR);
        $query->bindValue(':description', $obj->GetDescription(), PDO::PARAM_STR);
        $query->bindValue(':institution', $obj->getInstitution(), PDO::PARAM_STR);
        $query->bindValue(':linkage', $obj->getLinkage(), PDO::PARAM_STR);
        $query->bindValue(':quantity', $obj->getQuantity(), PDO::PARAM_STR);
        $query->bindValue(':unit', $obj->GetUnit(), PDO::PARAM_STR);
        $query->bindValue(':unitValue', $obj->getUnitValue(), PDO::PARAM_STR);
        $query->bindValue(':totalValue', $obj->GetTotalValue(), PDO::PARAM_STR);
        $query->bindValue(':status', $obj->GetStatus(), PDO::PARAM_STR);
        $query->bindValue(':id', $this->identityMap[$obj], PDO::PARAM_STR);

        try {
            $query->execute();
        } catch (Exception $e) {
            throw new C3op_Projects_MaterialSupplyException("$sql failed");
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

        $query = $this->db->prepare('SELECT action, description, institution, linkage, quantity, unit, unit_value, total_value, status FROM projects_material_supplies WHERE id = :id;');
        $query->bindValue(':id', $id, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();

        if (empty($result)) {
            throw new C3op_Projects_MaterialSupplyMapperException(sprintf('There is no Human Resource with id #%d.', $id));
        }
        $obj = new C3op_Projects_MaterialSupply();

        $this->setAttributeValue($obj, $id, 'id');
        $this->setAttributeValue($obj, $result['action'], 'action');
        $this->setAttributeValue($obj, $result['description'], 'description');
        $this->setAttributeValue($obj, $result['institution'], 'institution');
        $this->setAttributeValue($obj, $result['linkage'], 'linkage');
        $this->setAttributeValue($obj, $result['quantity'], 'quantity');
        $this->setAttributeValue($obj, $result['unit'], 'unit');
        $this->setAttributeValue($obj, $result['unit_value'], 'unitValue');
        $this->setAttributeValue($obj, $result['total_value'], 'totalValue');
        $this->setAttributeValue($obj, $result['status'], 'status');

        $this->identityMap[$obj] = $id;
        return $obj;

        $this->FetchDates($obj);
    }

    public function delete(C3op_Projects_MaterialSupply $i) {
        if (!isset($this->identityMap[$i])) {
            throw new C3op_Projects_MaterialSupplyMapperException('Object has no ID, cannot delete.');
        }
        $this->db->exec(
            sprintf(
                'DELETE FROM projects_material_supplies WHERE id = %d;',
                $this->identityMap[$i]
            )
        );
        unset($this->identityMap[$i]);
    }

     public function getAllMaterialSuppliesOnAction(C3op_Projects_Action $a) {
        $result = array();
            foreach ($this->db->query(
                    sprintf('SELECT id FROM projects_material_supplies WHERE action = %d;', $a->GetId())) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

   private function setAttributeValue(C3op_Projects_MaterialSupply $i, $fieldValue, $attributeName)
    {
        $attribute = new ReflectionProperty($i, $attributeName);
        $attribute->setAccessible(TRUE);
        $attribute->setValue($i, $fieldValue);
    }

    public function getAllOutlays(C3op_Projects_MaterialSupply $obj)
    {
        $result = array();
        return $result;


        foreach ($this->db->query(
                sprintf(
                    'SELECT id FROM projects_outlays WHERE team_member = %d;',
                    $obj->GetId()
                    )
                )
                as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function getSumOfPayedOutlays(C3op_Projects_MaterialSupply $obj)
    {
        $result = array();
        return $result;


        $query = $this->db->prepare('SELECT SUM(real_value) as sum FROM projects_outlays WHERE team_member = :id AND real_value > 0 AND real_date IS NOT NULL AND real_date <> "0000-00-00";');
        $query->bindValue(':id', $obj->GetId(), PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();
        return $result['sum'];
    }

    public function getSumOfProvidedButNotPayedOutlays(C3op_Projects_MaterialSupply $obj)
    {
        $result = array();
        return $result;


        $query = $this->db->prepare('SELECT SUM(predicted_value) as sum FROM projects_outlays WHERE team_member = :id AND predicted_value > 0 AND (real_value IS NULL OR real_value = 0.0);');
        $query->bindValue(':id', $obj->GetId(), PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();
        return $result['sum'];
    }



}