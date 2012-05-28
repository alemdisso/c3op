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
            'requirement_for_receiving' => $new->GetRequirementForReceiving(),
            'subordinated_to' => $new->GetSubordinatedTo()
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
                'UPDATE projects_actions SET title = \'%s\', project = %d, milestone = %d, requirement_for_receiving = %d, subordinated_to = %d  WHERE id = %d;',
                $a->GetTitle(),
                $a->GetProject(),
                $a->GetMilestone(),
                $a->GetRequirementForReceiving(),
                $a->GetSubordinatedTo(),
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
                'SELECT title, project, milestone, requirement_for_receiving, subordinated_to FROM projects_actions WHERE id = %d;',
                $id
            )
        );
        if (empty($result)) {
            throw new C3op_Projects_ActionMapperException(sprintf('There is no action with id #%d.', $id));
        }
        $title = $result['title'];
        $project = $result['project'];
        
        $a = new C3op_Projects_Action($project);
        $this->setAttributeValue($a, $id, 'id');
        $this->setAttributeValue($a, $result['title'], 'title');
        $this->setAttributeValue($a, $result['project'], 'project');
        $this->setAttributeValue($a, $result['milestone'], 'milestone');
        $this->setAttributeValue($a, $result['requirement_for_receiving'], 'requirementForReceiving');
        $this->setAttributeValue($a, $result['subordinated_to'], 'subordinatedTo');

        $this->identityMap[$a] = $id;
        return $a;        

    }

    public function delete(C3op_Projects_Action $a)
    {
        if (!isset($this->identityMap[$a])) {
            throw new C3op_Projects_ActionMapperException('Object has no ID, cannot delete.');
        }
        $this->db->exec(
            sprintf(
                'DELETE FROM projects_actions WHERE id = %d;',
                $this->identityMap[$a]
            )
        );
        unset($this->identityMap[$a]);
    }
    
    public function getAllOtherActions(C3op_Projects_Action $a)
    {
        $result = array();
        foreach ($this->db->query(
                sprintf(
                    'SELECT id FROM projects_actions WHERE project = %d AND id != %d;',
                    $a->GetProject(),
                    $a->GetId()
                    )
                )
                as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }
    
    public function getActionsSubordinatedTo(C3op_Projects_Action $a)
    {
        $result = array();
        foreach ($this->db->query(
                sprintf(
                    'SELECT id FROM projects_actions WHERE subordinated_to = %d;',
                    $a->GetId()
                    )
                )
                as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    private function setAttributeValue(C3op_Projects_Action $a, $fieldValue, $attributeName)
    {
        $attribute = new ReflectionProperty($a, $attributeName);
        $attribute->setAccessible(TRUE);
        $attribute->setValue($a, $fieldValue);
    }
    
    
}