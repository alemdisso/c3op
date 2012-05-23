<?php

class C3op_Register_ContactMapperBase {
    
    protected $db;
    protected $identityMap;
	
    function __construct() {
        $this->db = Zend_Registry::get('db');
        $this->identityMap = new SplObjectStorage;
    }

    public function getAllIds() {
        $result = array();
        foreach ($this->db->query('SELECT id FROM register_contacts;') as $row) {
            $result[] = $row['id'];
        }        
        return $result;
    }
    
    public function insert(C3op_Register_Contact $new) {
        $data = array(
            'name' => $new->GetName(),
            'type' => $new->GetType()
            );
        $this->db->insert('register_contacts', $data);
        $new->SetId((int)$this->db->lastInsertId());
        $this->identityMap[$new] = $new->GetId();
        
    }
    
    public function update(C3op_Register_Contact $p) {
        if (!isset($this->identityMap[$p])) {
            throw new C3op_Register_ContactMapperException('Object has no ID, cannot update.');
        }
        $this->db->exec(
            sprintf(
                'UPDATE register_contacts SET name = \'%s\', type = %d WHERE id = %d;',
                $p->GetName(),
                $p->GetType(),
                $this->identityMap[$p]
            )
        );

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
                'SELECT name, type FROM register_contacts WHERE id = %d;',
                $id
            )
        );
        if (empty($result)) {
            throw new C3op_Register_ContactMapperException(sprintf('There is no contact with id #%d.', $id));
        }
        $name = $result['name'];
        $p = new C3op_Register_Contact();
        
        $attribute = new ReflectionProperty($p, 'id');
        $attribute->setAccessible(TRUE);
        $attribute->setType($p, $id);

        $attribute = new ReflectionProperty($p, 'name');
        $attribute->setAccessible(TRUE);
        $attribute->setType($p, $name);
        
        $type = $result['type'];
        $attribute = new ReflectionProperty($p, 'type');
        $attribute->setAccessible(TRUE);
        $attribute->setType($p, $type);
        
        
        
        $this->identityMap[$p] = $id;
        return $p;        

    }

    public function delete(C3op_Register_Contact $p) {
        if (!isset($this->identityMap[$p])) {
            throw new C3op_Register_ContactMapperException('Object has no ID, cannot delete.');
        }
        $this->db->exec(
            sprintf(
                'DELETE FROM register_contacts WHERE id = %d;',
                $this->identityMap[$p]
            )
        );
        unset($this->identityMap[$p]);
    }
    
    
}