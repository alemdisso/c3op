<?php

class C3op_Projects_HumanResourceMapper {

    protected $db;
    protected $identityMap;

    function __construct() {
        $this->db = Zend_Registry::get('db');
        $this->identityMap = new SplObjectStorage;
    }

    public function getAllIds() {
        $result = array();
            foreach ($this->db->query('SELECT id FROM projects_team_members;') as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function insert(C3op_Projects_HumanResource $new) {


        $query = $this->db->prepare("INSERT INTO projects_team_members (action, contact, description, value, status) VALUES (:action, :contact, :description, :value, :status)");

        $query->bindValue(':action', $new->GetAction(), PDO::PARAM_INT);
        $query->bindValue(':contact', $new->GetContact(), PDO::PARAM_INT);
        $query->bindValue(':description', $new->GetDescription(), PDO::PARAM_STR);
        $query->bindValue(':value', $new->GetValue(), PDO::PARAM_STR);
        $query->bindValue(':status', $new->GetStatus(), PDO::PARAM_INT);

        $query->execute();

        $new->SetId((int)$this->db->lastInsertId());
        $this->identityMap[$new] = $new->GetId();

    }

    public function update(C3op_Projects_HumanResource $obj) {
        if (!isset($this->identityMap[$obj])) {
            throw new C3op_Projects_HumanResourceMapperException('Object has no ID, cannot update.');
        }

        $query = $this->db->prepare("UPDATE projects_team_members SET action = :action, description = :description, contact = :contact, value = :value, status = :status WHERE id = :id;");

        $query->bindValue(':action', $a->GetAction(), PDO::PARAM_STR);
        $query->bindValue(':description', $a->GetDescription(), PDO::PARAM_STR);
        $query->bindValue(':contact', $a->GetContact(), PDO::PARAM_STR);
        $query->bindValue(':value', $a->GetValue(), PDO::PARAM_STR);
        $query->bindValue(':status', $a->GetStatus(), PDO::PARAM_STR);
        $query->bindValue(':id', $this->identityMap[$obj], PDO::PARAM_STR);

        try {
            $query->execute();
        } catch (Exception $e) {
            throw new C3op_Projects_HumanResourceException("$sql failed");
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

        $result = $this->db->fetchRow(
            sprintf(
                'SELECT action, description, contact, value, status
                     FROM projects_team_members WHERE id = %d;',
                $id
            )
        );
        if (empty($result)) {
            throw new C3op_Projects_HumanResourceMapperException(sprintf('There is no Human Resource with id #%d.', $id));
        }
        $obj = new C3op_Projects_HumanResource();

        $this->setAttributeValue($obj, $id, 'id');
        $this->setAttributeValue($obj, $result['action'], 'action');
        $this->setAttributeValue($obj, $result['description'], 'description');
        $this->setAttributeValue($obj, $result['contact'], 'contact');
        $this->setAttributeValue($obj, $result['value'], 'value');
        $this->setAttributeValue($obj, $result['status'], 'status');

        $this->identityMap[$obj] = $id;
        return $obj;

        $this->FetchDates($obj);
    }

    public function delete(C3op_Projects_HumanResource $i) {
        if (!isset($this->identityMap[$i])) {
            throw new C3op_Projects_HumanResourceMapperException('Object has no ID, cannot delete.');
        }
        $this->db->exec(
            sprintf(
                'DELETE FROM projects_team_members WHERE id = %d;',
                $this->identityMap[$i]
            )
        );
        $this->db->exec(
            sprintf(
                'DELETE FROM projects_team_members_dates WHERE human_resource = %d;',
                $this->identityMap[$a]
            )
        );
        unset($this->identityMap[$i]);
    }

     public function getAllHumanResourcesOnAction(C3op_Projects_Action $a) {
        $result = array();
            foreach ($this->db->query(
                    sprintf('SELECT id FROM projects_team_members WHERE action = %d;', $a->GetId())) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

   private function setAttributeValue(C3op_Projects_HumanResource $i, $fieldValue, $attributeName)
    {
        $attribute = new ReflectionProperty($i, $attributeName);
        $attribute->setAccessible(TRUE);
        $attribute->setValue($i, $fieldValue);
    }

    public function getAllOutlays(C3op_Projects_HumanResource $h)
    {
        $result = array();
        foreach ($this->db->query(
                sprintf(
                    'SELECT id FROM projects_outlays WHERE human_resource = %d;',
                    $h->GetId()
                    )
                )
                as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }


}