<?php

class C3op_Projects_ActionEventMapper
{
    
    protected $db;
    protected $action;
    protected $identityMap;
	
    function __construct(C3op_Projects_Action $action)
    {
        $this->action = $action;
        $this->db = Zend_Registry::get('db');
        $this->identityMap = new SplObjectStorage;
    }

    public function getAllIds()
    {
        $result = array();
        foreach ($this->db->query(
                    sprintf('SELECT id FROM projects_actions_events WHERE action = %d;',
                            $this->action->GetId())
                 ) as $row) {
            $result[] = $row['id'];
        }        
        return $result;
    }
    
    public function insert(C3op_Projects_ActionEvent $new)
    {

        $data = array(
            'action' => $this->action->GetId(),
            'type' => $new->GetType(),
            'timestamp' => date('Y-m-d H:i:s'),
            'observation' => $new->GetObservation(),
            'responsible' => $new->GetResponsible(),
            );
        $this->db->insert('projects_actions_events', $data);
        
        $new->SetId((int)$this->db->lastInsertId());
        $this->identityMap[$new] = $new->GetId();
    }
    
    public function update(C3op_Projects_ActionEvent $obj)
    {
        if (!isset($this->identityMap[$obj])) {
            throw new C3op_Projects_ActionEventMapperException('Object has no ID, cannot update.');
        }

        $this->db->exec(
            sprintf(
                'UPDATE projects_actions_events SET action = %d, type = %d, done = \'%s\', observation = \'%s\', responsible = %d WHERE id = %d;',
                $obj->GetAction(),
                $obj->GetType(),
                $obj->GetTimestamp(),
                $obj->GetObservation(),
                $obj->GetResponsible(),
                $this->identityMap[$obj]
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
                'SELECT action, type, timestamp, observation, responsible FROM projects_actions WHERE id = %d;',
                $id
            )
        );
        if (empty($result)) {
            throw new C3op_Projects_ActionEventMapperException(sprintf('There is no action\'s event with id #%d.', $id));
        }
        $action = $result['action'];
        $type = $result['type'];
        
        $obj = new C3op_Projects_ActionEvent($action);
        $this->setAttributeValue($obj, $id, 'id');
        $this->setAttributeValue($obj, $result['action'], 'action');
        $this->setAttributeValue($obj, $result['type'], 'type');
        $this->setAttributeValue($obj, $result['timestamp'], 'timestamp');
        $this->setAttributeValue($obj, $result['observation'], 'observation');
        $this->setAttributeValue($obj, $result['responsible'], 'responsible');
        

        $this->identityMap[$obj] = $id;
        
        return $obj;        

    }

    public function delete(C3op_Projects_ActionEvent $obj)
    {
        if (!isset($this->identityMap[$obj])) {
            throw new C3op_Projects_ActionEventMapperException('Object has no ID, cannot delete.');
        }
        $this->db->exec(
            sprintf(
                'DELETE FROM projects_actions_events WHERE id = %d;',
                $this->identityMap[$obj]
            )
        );
        unset($this->identityMap[$obj]);
    }
    
}