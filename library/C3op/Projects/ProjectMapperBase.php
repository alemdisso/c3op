<?php

class C3op_Projects_ProjectMapperBase {
    
    protected $db;
    protected $identityMap;
	
    function __construct() {
        $this->db = Zend_Registry::get('db');
        $this->identityMap = new SplObjectStorage;
    }

    public function getAllIds() {
        $result = array();
        foreach ($this->db->query('SELECT id FROM projects_projects;') as $row) {
            $result[] = $row['id'];
        }        
        return $result;
    }
    
    public function insert(C3op_Projects_Project $new) {
        $data = array(
            'title' => $new->getTitle(),
            'date_begin' => $new->GetDateBegin(),
            'value' => $new->GetValue()
            );
        $this->db->insert('projects_projects', $data);
        $new->SetId((int)$this->db->lastInsertId());
        $this->identityMap[$new] = $new->GetId();
        
    }
    
    public function update(C3op_Projects_Project $p) {
        if (!isset($this->identityMap[$p])) {
            throw new C3op_Projects_ProjectMapperException('Object has no ID, cannot update.');
        }
        $this->db->exec(
            sprintf(
                'UPDATE projects_projects SET title = \'%s\', date_begin = \'%s\', value = %f WHERE id = %d;',
                $p->GetTitle(),
                $p->GetDateBegin(),
                $p->GetValue(),
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
                'SELECT title, date_begin, value FROM projects_projects WHERE id = %d;',
                $id
            )
        );
        if (empty($result)) {
            throw new C3op_Projects_ProjectMapperException(sprintf('There is no project with id #%d.', $id));
        }
        $p = new C3op_Projects_Project();
        
        $this->setAttributeValue($p, $id, 'id');
        $this->setAttributeValue($p, $result['title'], 'title');
        $this->setAttributeValue($p, $result['date_begin'], 'dateBegin');
        $this->setAttributeValue($p, $result['value'], 'value');

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
    
    public function getAllActions(C3op_Projects_Project $p)
    {
        $result = array();
        foreach ($this->db->query(
                sprintf(
                    'SELECT id FROM projects_actions WHERE project = %d;',
                    $p->GetId()
                    )
                )
                as $row) {
            $result[] = $row['id'];
        }        

        return $result;
    }
    
    public function getAllProducts(C3op_Projects_Project $p)
    {
        $result = array();
        foreach ($this->db->query(
                sprintf(
                    'SELECT id FROM projects_actions WHERE project = %d AND subordinated_to IS NULL AND requirement_for_receiving = 1;',
                    $p->GetId()
                    )
                )
                as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function getAllActionsSubordinatedTo(C3op_Projects_Project $p, $actionId=0)
    {
        if ($actionId >= 0) {
            $result = array();
            foreach ($this->db->query(
                    sprintf(
                        'SELECT id FROM projects_actions WHERE project = %d AND (subordinated_to IS NULL OR subordinated_to = %d);',
                        $p->GetId(),
                        $actionId
                        )
                    )
                    as $row) {
                $result[] = $row['id'];
            }        

            return $result;
        } else throw new C3op_Projects_ActionMapperException("invalid action id to find subordinated for");
    }
 
    private function setAttributeValue(C3op_Projects_Project $p, $fieldValue, $attributeName)
    {
        $attribute = new ReflectionProperty($p, $attributeName);
        $attribute->setAccessible(TRUE);
        $attribute->setValue($p, $fieldValue);
    }

}