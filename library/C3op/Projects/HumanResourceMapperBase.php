<?php

class C3op_Projects_HumanResourceMapperBase {
    
    protected $db;
    protected $identityMap;
	
    function __construct() {
        $this->db = Zend_Registry::get('db');
        $this->identityMap = new SplObjectStorage;
    }

    public function getAllIds() {
        $result = array();
            foreach ($this->db->query('SELECT id FROM projects_human_resources;') as $row) {
            $result[] = $row['id'];
        }        
        return $result;
    }
    
    public function insert(C3op_Projects_HumanResource $new) {
        $data = array(
            'action' => $new->GetAction(),
            'contact' => $new->GetContact(),
            'description' => $new->GetDescription(),
            'value' => $new->GetValue(),
            'status' => $new->GetStatus(),
            );
        $this->db->insert('projects_human_resources', $data);
        $new->SetId((int)$this->db->lastInsertId());
        $this->identityMap[$new] = $new->GetId();
        
    }
    
    public function update(C3op_Projects_HumanResource $i) {
        if (!isset($this->identityMap[$i])) {
            throw new C3op_Projects_HumanResourceMapperException('Object has no ID, cannot update.');
        }
        $sql = 
                sprintf(
                    'UPDATE projects_human_resources SET action = %d, 
                        description =  \'%s\',
                        contact = %d, 
                        value =  %f,
                        status = %d
                         WHERE id = %d;',
                    $i->GetAction(),
                    $i->GetDescription(),
                    $i->GetContact(),
                    $i->GetValue(),
                    $i->GetStatus(),
                    $this->identityMap[$i]
                );
        try {
            $this->db->exec($sql);
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
                     FROM projects_human_resources WHERE id = %d;',
                $id
            )
        );
        if (empty($result)) {
            throw new C3op_Projects_HumanResourceMapperException(sprintf('There is no Human Resource with id #%d.', $id));
        }
        $i = new C3op_Projects_HumanResource();
        
        $this->setAttributeValue($i, $id, 'id');
        $this->setAttributeValue($i, $result['action'], 'action');
        $this->setAttributeValue($i, $result['description'], 'description');
        $this->setAttributeValue($i, $result['contact'], 'contact');
        $this->setAttributeValue($i, $result['value'], 'value');
        $this->setAttributeValue($i, $result['status'], 'status');

        $this->identityMap[$i] = $id;
        return $i;        

    }

    public function delete(C3op_Projects_HumanResource $i) {
        if (!isset($this->identityMap[$i])) {
            throw new C3op_Projects_HumanResourceMapperException('Object has no ID, cannot delete.');
        }
        $this->db->exec(
            sprintf(
                'DELETE FROM projects_human_resources WHERE id = %d;',
                $this->identityMap[$i]
            )
        );
        unset($this->identityMap[$i]);
    }

     public function getAllHumanResourcesOnAction(C3op_Projects_Action $a) {
        $result = array();
            foreach ($this->db->query(
                    sprintf('SELECT id FROM projects_human_resources WHERE action = %d;', $a->GetId())) as $row) {
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