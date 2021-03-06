<?php

class C3op_Resources_MaterialSupplyMapper {

    protected $db;
    protected $identityMap;

    function __construct() {
        $this->db = Zend_Registry::get('db');
        $this->identityMap = new SplObjectStorage;
    }

    public function getAllIds() {
        $result = array();
            foreach ($this->db->query('SELECT id FROM resources_material_supplies;') as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function insert(C3op_Resources_MaterialSupply $new) {


        $query = $this->db->prepare("INSERT INTO resources_material_supplies (
            action, project, description, institution, linkage, quantity, unit, unit_value, total_value, status) VALUES (:action, :project, :description, :institution, :linkage, :quantity, :unit, :unitValue, :totalValue, :status)");

        $query->bindValue(':action', $new->GetAction(), PDO::PARAM_INT);
        $query->bindValue(':project', $new->GetProject(), PDO::PARAM_INT);
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

    public function update(C3op_Resources_MaterialSupply $obj) {
        if (!isset($this->identityMap[$obj])) {
            throw new C3op_Resources_MaterialSupplyMapperException('Object has no ID, cannot update.');
        }

        $query = $this->db->prepare("UPDATE resources_material_supplies SET action = :action, project = :project, description = :description, institution = :institution, linkage = :linkage, quantity = :quantity, unit = :unit, unit_value = :unitValue, total_value = :totalValue, status = :status WHERE id = :id;");

        $query->bindValue(':action', $obj->GetAction(), PDO::PARAM_STR);
        $query->bindValue(':project', $obj->GetProject(), PDO::PARAM_STR);
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
            throw new C3op_Resources_MaterialSupplyException("$sql failed");
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

        $query = $this->db->prepare('SELECT action, project, description, institution, linkage, quantity, unit, unit_value, total_value, status FROM resources_material_supplies WHERE id = :id;');
        $query->bindValue(':id', $id, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();

        if (empty($result)) {
            throw new C3op_Resources_MaterialSupplyMapperException(sprintf('There is no Human Resource with id #%d.', $id));
        }
        $obj = new C3op_Resources_MaterialSupply();

        $this->setAttributeValue($obj, $id, 'id');
        $this->setAttributeValue($obj, $result['action'], 'action');
        $this->setAttributeValue($obj, $result['project'], 'project');
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

    public function delete(C3op_Resources_MaterialSupply $i) {
        if (!isset($this->identityMap[$i])) {
            throw new C3op_Resources_MaterialSupplyMapperException('Object has no ID, cannot delete.');
        }
        $this->db->exec(
            sprintf(
                'DELETE FROM resources_material_supplies WHERE id = %d;',
                $this->identityMap[$i]
            )
        );
        unset($this->identityMap[$i]);
    }

     public function getAllMaterialSuppliesOnAction(C3op_Projects_Action $a) {
        $result = array();
            foreach ($this->db->query(
                    sprintf('SELECT id FROM resources_material_supplies WHERE action = %d;', $a->GetId())) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

   private function setAttributeValue(C3op_Resources_MaterialSupply $i, $fieldValue, $attributeName)
    {
        $attribute = new ReflectionProperty($i, $attributeName);
        $attribute->setAccessible(TRUE);
        $attribute->setValue($i, $fieldValue);
    }

    public function getAllOutlays(C3op_Resources_MaterialSupply $obj)
    {
        $result = array();
        return $result;


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

    public function getSumOfPayedOutlays(C3op_Resources_MaterialSupply $obj)
    {
        $result = array();
        return $result;


        $query = $this->db->prepare('SELECT SUM(real_value) as sum FROM finances_outlays WHERE responsible = :id AND real_value > 0 AND real_date IS NOT NULL;');
        $query->bindValue(':id', $obj->GetId(), PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();
        return $result['sum'];
    }

    public function getSumOfProvidedButNotPayedOutlays(C3op_Resources_MaterialSupply $obj)
    {
        $result = array();
        return $result;


        $query = $this->db->prepare('SELECT SUM(predicted_value) as sum FROM finances_outlays WHERE responsible = :id AND predicted_value > 0 AND (real_value IS NULL OR real_value = 0.0);');
        $query->bindValue(':id', $obj->GetId(), PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();
        return $result['sum'];
    }


    public function getAllMaterialSuppliesContractedOrPredictedAt(C3op_Projects_Project $obj) {
        $result = array();

        foreach ($this->db->query(sprintf('SELECT s.id
            FROM projects_actions a
            INNER JOIN resources_material_supplies s ON a.id = s.action
            WHERE a.project = %d
            AND (
            s.status = %d
            OR s.status = %d
            OR s.status = %d
            OR s.status = %d
            )'
            , $obj->getId()
            , C3op_Resources_MaterialSupplyStatusConstants::STATUS_UNDEFINED
            , C3op_Resources_MaterialSupplyStatusConstants::STATUS_CONTRACTED
            , C3op_Resources_MaterialSupplyStatusConstants::STATUS_ACQUITTED
            , C3op_Resources_MaterialSupplyStatusConstants::STATUS_FORESEEN
                )) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function getMaterialSuppliesValueJustForThisAction(C3op_Projects_Action $obj)
    {
        $query = $this->db->prepare('SELECT SUM(total_value) as value FROM resources_material_supplies WHERE action = :action AND (
            status = :foreseen OR status = :contracted OR status = :acquited);');
        $query->bindValue(':action', $obj->GetId(), PDO::PARAM_STR);
        $query->bindValue(':foreseen', C3op_Resources_ResponsibleStatusConstants::STATUS_FORESEEN, PDO::PARAM_STR);
        $query->bindValue(':contracted', C3op_Resources_ResponsibleStatusConstants::STATUS_CONTRACTED, PDO::PARAM_STR);
        $query->bindValue(':acquited', C3op_Resources_ResponsibleStatusConstants::STATUS_ACQUITTED, PDO::PARAM_STR);
        $query->execute();
        $resultPDO = $query->fetchAll();

        foreach ($resultPDO as $row) {
            if (!is_null($row['value'])) {
                return $row['value'];
            } else {
                return 0;
            }
        }
        return 0;
    }

    public function getContractedMaterialSuppliesValueJustForThisAction(C3op_Projects_Action $obj)
    {
        $query = $this->db->prepare('SELECT SUM(total_value) as value FROM resources_material_supplies WHERE action = :action AND (
            status = :contracted OR status = :acquited);');
        $query->bindValue(':action', $obj->GetId(), PDO::PARAM_STR);
        $query->bindValue(':contracted', C3op_Resources_ResponsibleStatusConstants::STATUS_CONTRACTED, PDO::PARAM_STR);
        $query->bindValue(':acquited', C3op_Resources_ResponsibleStatusConstants::STATUS_ACQUITTED, PDO::PARAM_STR);
        $query->execute();
        $resultPDO = $query->fetchAll();

        foreach ($resultPDO as $row) {
            if (!is_null($row['value'])) {
                return $row['value'];
            } else {
                return 0;
            }
        }
        return 0;
    }




}