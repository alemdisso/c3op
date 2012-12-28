<?php

class C3op_Projects_ContractMapper
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
        $query = $this->db->prepare('SELECT id FROM projects_contracts WHERE 1=1;');
        $query->execute();
        $resultPDO = $query->fetchAll();

        $result = array();
        foreach ($resultPDO as $row) {
            $result[] = $row['id'];
        }
        return $result;

    }

    public function insert(C3op_Projects_Contract $obj)
    {

        $query = $this->db->prepare("INSERT INTO projects_contracts (
            project, amendment, begin_date, finish_date, signing_date,
            value, contract_nature, management_fee, object, summary, observation)
            VALUES (:project, :amendment, :begin_date, :finish_date, :signing_date,
            :value, :contract_nature, :management_fee, :object, :summary, :observation)");

        $query->bindValue(':project', $obj->getProject(), PDO::PARAM_STR);
        $query->bindValue(':amendment', $obj->getAmendment(), PDO::PARAM_STR);
        $query->bindValue(':begin_date', $obj->getBeginDate(), PDO::PARAM_STR);
        $query->bindValue(':finish_date', $obj->getFinishDate(), PDO::PARAM_STR);
        $query->bindValue(':signing_date', $obj->getSigningDate(), PDO::PARAM_STR);
        $query->bindValue(':value', $obj->getValue(), PDO::PARAM_STR);
        $query->bindValue(':contract_nature', $obj->getContractNature(), PDO::PARAM_STR);
        $query->bindValue(':management_fee', $obj->getManagementFee(), PDO::PARAM_STR);
        $query->bindValue(':object', $obj->getObject(), PDO::PARAM_STR);
        $query->bindValue(':summary', $obj->getSummary(), PDO::PARAM_STR);
        $query->bindValue(':observation', $obj->getObservation(), PDO::PARAM_STR);

        $query->execute();

        $obj->setId((int)$this->db->lastInsertId());
        $this->identityMap[$obj] = $obj->getId();

    }

    public function update(C3op_Projects_Contract $obj)
    {
        if (!isset($this->identityMap[$obj])) {
            throw new C3op_Projects_ContractMapperException('Object has no ID, cannot update.');
        }

        $query = $this->db->prepare("UPDATE projects_contracts SET project = :project, amendment = :amendment,
            begin_date = :begin_date, finish_date = :finish_date, signing_date = :signing_date,value = :value,
            management_fee = :management_fee, object = :object, summary = :summary, observation = :observation
            WHERE id = :id;");

        $query->bindValue(':project', $obj->getProject(), PDO::PARAM_STR);
        $query->bindValue(':begin_date', $obj->getBeginDate(), PDO::PARAM_STR);
        $query->bindValue(':finish_date', $obj->getFinishDate(), PDO::PARAM_STR);
        $query->bindValue(':value', $obj->getValue(), PDO::PARAM_STR);
        $query->bindValue(':contract_nature', $obj->getContractNature(), PDO::PARAM_STR);
        $query->bindValue(':title', $obj->getTitle(), PDO::PARAM_STR);
        $query->bindValue(':signing_date', $obj->getSigningDate(), PDO::PARAM_STR);
        $query->bindValue(':amendment', $obj->getAmendment(), PDO::PARAM_STR);
        $query->bindValue(':management_fee', $obj->getManagementFee(), PDO::PARAM_STR);
        $query->bindValue(':object', $obj->getObject(), PDO::PARAM_STR);
        $query->bindValue(':summary', $obj->getSummary(), PDO::PARAM_STR);
        $query->bindValue(':observation', $obj->getObservation(), PDO::PARAM_STR);
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

        $query = $this->db->prepare('SELECT project
                    , amendment
                    , begin_date
                    , finish_date
                    , signing_date
                    , value
                    , contract_nature
                    , management_fee
                    , object
                    , summary
                    , observation
                    FROM projects_contracts WHERE id = :id;');
        $query->bindValue(':id', $id, PDO::PARAM_STR);
        $query->execute();

        $result = $query->fetch();

        if (empty($result)) {
            throw new C3op_Projects_ContractMapperException(sprintf('There is no contract with id #%d.', $id));
        }
        $project = $result['project'];

        $obj = new C3op_Projects_Contract($result['project'], $result['signing_date'], $result['amendment'], $id);
        $this->setAttributeValue($obj, $id, 'id');
        $this->setAttributeValue($obj, $result['amendment'], 'amendment');
        $this->setAttributeValue($obj, $result['begin_date'], 'beginDate');
        $this->setAttributeValue($obj, $result['finish_date'], 'finishDate');
        $this->setAttributeValue($obj, $result['signing_date'], 'signing_date');
        $this->setAttributeValue($obj, $result['status'], 'status');
        $this->setAttributeValue($obj, $result['value'], 'value');
        $this->setAttributeValue($obj, $result['contract_nature'], 'contractNature');
        $this->setAttributeValue($obj, $result['management_fee'], 'managementFee');
        $this->setAttributeValue($obj, $result['object'], 'object');
        $this->setAttributeValue($obj, $result['summary'], 'summary');
        $this->setAttributeValue($obj, $result['observation'], 'observation');

        $this->identityMap[$obj] = $id;

        return $obj;

    }

    public function delete(C3op_Projects_Contract $obj)
    {
        if (!isset($this->identityMap[$obj])) {
            throw new C3op_Projects_ContractMapperException('Object has no ID, cannot delete.');
        }
        $query = $this->db->prepare('DELETE FROM projects_contracts WHERE id = :id;');
        $query->bindValue(':id', $this->identityMap[$obj], PDO::PARAM_STR);
        $query->execute();
        unset($this->identityMap[$obj]);
    }


    private function setAttributeValue(C3op_Projects_Contract $a, $fieldValue, $attributeName)
    {
        $attribute = new ReflectionProperty($a, $attributeName);
        $attribute->setAccessible(TRUE);
        $attribute->setValue($a, $fieldValue);
    }


}