<?php

class C3op_Register_LinkageMapper
{
    
    protected $db;
    protected $identityMap;
	
    function __construct() {
        $this->db = Zend_Registry::get('db');
        $this->identityMap = new SplObjectStorage;
    }

    public function getAllIds() {
        $result = array();
            foreach ($this->db->query('SELECT id FROM register_linkages;') as $row) {
            $result[] = $row['id'];
        }        
        return $result;
    }
    
    public function insert(C3op_Register_Linkage $new) {
        $data = array(
            'contact' => $new->GetContact(),
            'institution' => $new->GetInstitution(),
            'department' => $new->GetDepartment(),
            'state' => $new->GetState(),
            'position' => $new->GetPosition(),
            );
        $this->db->insert('register_linkages', $data);
        $new->SetId((int)$this->db->lastInsertId());
        $this->identityMap[$new] = $new->GetId();
        
    }
    
    public function update(C3op_Register_Linkage $i) {
        if (!isset($this->identityMap[$i])) {
            throw new C3op_Register_LinkageMapperException('Object has no ID, cannot update.');
        }
        $sql = 
                sprintf(
                    'UPDATE register_linkages SET contact = %d, 
                        institution =  %d,
                        department =  \'%s\',
                        state =  \'%s\',
                        position =  \'%s\' 
                         WHERE id = %d;',
                    $i->GetContact(),
                    $i->GetInstitution(),
                    $i->GetDepartment(),
                    $i->GetState(),
                    $i->GetPosition(),
                    $this->identityMap[$i]
                );
        try {
            $this->db->exec($sql);
        } catch (Exception $e) {
            throw new C3op_Register_LinkageException("$sql failed");
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
                'SELECT contact, institution, department, state, position
                     FROM register_linkages WHERE id = %d;',
                $id
            )
        );
        if (empty($result)) {
            throw new C3op_Register_LinkageMapperException(sprintf('There is no Linkage with id #%d.', $id));
        }
        $i = new C3op_Register_Linkage();
        
        $this->setAttributeValue($i, $id, 'id');
        $this->setAttributeValue($i, $result['contact'], 'contact');
        $this->setAttributeValue($i, $result['institution'], 'institution');
        $this->setAttributeValue($i, $result['department'], 'department');
        $this->setAttributeValue($i, $result['state'], 'state');
        $this->setAttributeValue($i, $result['position'], 'position');

        $this->identityMap[$i] = $id;
        return $i;        

    }

    public function delete(C3op_Register_Linkage $i) {
        if (!isset($this->identityMap[$i])) {
            throw new C3op_Register_LinkageMapperException('Object has no ID, cannot delete.');
        }
        $this->db->exec(
            sprintf(
                'DELETE FROM register_linkages WHERE id = %d;',
                $this->identityMap[$i]
            )
        );
        unset($this->identityMap[$i]);
    }

    private function setAttributeValue(C3op_Register_Linkage $i, $fieldValue, $attributeName)
    {
        $attribute = new ReflectionProperty($i, $attributeName);
        $attribute->setAccessible(TRUE);
        $attribute->setValue($i, $fieldValue);
    }

}