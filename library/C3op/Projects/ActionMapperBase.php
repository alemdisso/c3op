<?php

class C3op_Projects_ActionMapperBase
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
        $result = array();
        foreach ($this->db->query('SELECT id FROM projects_actions;') as $row) {
            $result[] = $row['id'];
        }        
        return $result;
    }
    
    public function insert(C3op_Projects_Action $new)
    {
        $data = array(
            'title' => $new->getTitle(),
            'project' => $new->GetProject(),
            'milestone' => $new->GetMilestone(),
            'requirement_for_receiving' => $new->GetRequirementForReceiving()
            );
        $this->db->insert('projects_actions', $data);
        $new->SetId((int)$this->db->lastInsertId());
        $this->identityMap[$new] = $new->GetId();
        
    }
    
    public function update(C3op_Projects_Action $a)
    {
        if (!isset($this->identityMap[$a])) {
            throw new C3op_Projects_ActionMapperException('Object has no ID, cannot update.');
        }
        $this->db->exec(
            sprintf(
                'UPDATE projects_actions SET title = \'%s\', project = %d, milestone = %d, requirement_for_receiving = %d  WHERE id = %d;',
                $a->GetTitle(),
                $a->GetProject(),
                $a->GetMilestone(),
                $a->GetRequirementForReceiving(),
                $this->identityMap[$a]
            )
        );

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
        
        $result = $this->db->fetchRow(
            sprintf(
                'SELECT title, project, milestone, requirement_for_receiving FROM projects_actions WHERE id = %d;',
                $id
            )
        );
        if (empty($result)) {
            throw new C3op_Projects_ActionMapperException(sprintf('There is no action with id #%d.', $id));
        }
        $title = $result['title'];
        $project = $result['project'];
        
        $a = new C3op_Projects_Action($project);
        $attribute = new ReflectionProperty($a, 'title');
        $attribute->setAccessible(TRUE);
        $attribute->setValue($a, $title);

        $attribute = new ReflectionProperty($a, 'project');
        $attribute->setAccessible(TRUE);
        $attribute->setValue($a, $project);
        
        $milestone = $result['milestone'];
        $attribute = new ReflectionProperty($a, 'milestone');
        $attribute->setAccessible(TRUE);
        $attribute->setValue($a, $milestone);

        $requirementForReceiving = $result['requirement_for_receiving'];
        $attribute = new ReflectionProperty($a, 'requirementForReceiving');
        $attribute->setAccessible(TRUE);
        $attribute->setValue($a, $requirementForReceiving);

        $this->identityMap[$a] = $id;
        return $a;        

    }

    public function delete(C3op_Projects_Project $p)
    {
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
    
    
    
}