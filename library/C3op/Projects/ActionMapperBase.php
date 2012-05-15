<?php

class C3op_Projects_ActionMapperBase {
    
    protected $db;
    protected $identityMap;
	
    function __construct() {
        $this->db = Zend_Registry::get('db');
        $this->identityMap = new SplObjectStorage;
    }

    public function getAllIds() {
        $result = array();
        foreach ($this->db->query('SELECT id FROM projects_actions;') as $row) {
            $result[] = $row['id'];
        }        
        return $result;
    }
    
    public function insert(C3op_Projects_Action $new) {
        $data = array(
            'title' => $new->getTitle(),
            'project' => $new->GetProject()
            );
        $this->db->insert('projects_actions', $data);
        $new->SetId((int)$this->db->lastInsertId());
        $this->identityMap[$new] = $new->GetId();
        
    }
    
    public function update(C3op_Projects_Action $a) {
        if (!isset($this->identityMap[$a])) {
            throw new C3op_Projects_ActionMapperException('Object has no ID, cannot update.');
        }
        $this->db->exec(
            sprintf(
                'UPDATE projects_actions SET title = \'%s\', project = %d,  WHERE id = %d;',
                $a->GetTitle(),
                $a->GetProject(),
                $this->identityMap[$a]
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
                'SELECT title, project FROM projects_actions WHERE id = %d;',
                $id
            )
        );
        if (empty($result)) {
            throw new C3op_Projects_ProjectMapperException(sprintf('There is no project with id #%d.', $id));
        }
        $title = $result['title'];
        $p = new C3op_Projects_Project();
        $attribute = new ReflectionProperty($p, 'title');
        $attribute->setAccessible(TRUE);
        $attribute->setValue($p, $title);

        $dateBegin = $result['date_begin'];
        $attribute = new ReflectionProperty($p, 'dateBegin');
        $attribute->setAccessible(TRUE);
        $attribute->setValue($p, $dateBegin);
        
        $value = $result['value'];
        $attribute = new ReflectionProperty($p, 'value');
        $attribute->setAccessible(TRUE);
        $attribute->setValue($p, $value);
        
        
        
        $this->identityMap[$p] = $id;
        return $p;        

    }

    public function delete(C3op_Projects_Project $p) {
        if (!isset($this->identityMap[$p])) {
            throw new C3op_Projects_ProjectMapperException('Object has no ID, cannot delete.');
        }
        $this->db->exec(
            sprintf(
                'DELETE FROM projects_projects WHERE id = %d;',
                $this->identityMap[$p]
            )
        );
        unset($this->identityMap[$p]);
    }
    
//    public function detach(C3op_Projects_Project $p) {
//
//        $this->identityMap->detach($p);
//        print_r($this->identityMap);
//    }

    
}