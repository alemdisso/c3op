<?php

class C3op_Projects_OutlayMapper
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
        foreach ($this->db->query('SELECT id FROM projects_outlays;') as $row) {
            $result[] = $row['id'];
        }        
        return $result;
    }
    
    public function insert(C3op_Projects_Outlay $new)
    {
        $data = array(
            'project' => $new->GetProject(),
            'action' => $new->GetAction(),
            'human_resource' => $new->GetHumanResource(),
            'predicted_value' => $new->GetPredictedValue(),
            'predicted_date' => $new->GetPredictedDate(),
            'recurrent' => $new->GetRecurrent(),
            'observation' => $new->GetObservation(),
            );
        $this->db->insert('projects_outlays', $data);
        $new->SetId((int)$this->db->lastInsertId());
        $this->identityMap[$new] = $new->GetId();
        
    }
    
    public function update(C3op_Projects_Outlay $o)
    {
        if (!isset($this->identityMap[$o])) {
            throw new C3op_Projects_OutlayMapperException('Object has no ID, cannot update.');
        }
        
        $this->db->exec(
            sprintf(
                'UPDATE projects_outlays SET project = %d, action = %d, human_resource = %d, predicted_value = %.2f, predicted_date = \'%s\', recurrent = %d, observation = \'%s\' WHERE id = %d;',
                $o->GetProject(),
                $o->GetAction(),
                $o->GetHumanResource(),
                $o->GetPredictedValue(),
                $o->GetPredictedDate(),
                $o->GetRecurrent(),
                $o->GetObservation(),
                $this->identityMap[$o]
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
                'SELECT  project, action, human_resource, predicted_value, predicted_date, recurrent, observation FROM projects_outlays WHERE id = %d;',
                $id
            )
        );
        if (empty($result)) {
            throw new C3op_Projects_OutlayMapperException(sprintf('There is no outlay with id #%d.', $id));
        }
        $r = new C3op_Projects_Outlay($result['human_resource'], $id);
        $this->setAttributeValue($r, $id, 'id');
        $this->setAttributeValue($r, $result['project'], 'project');
        $this->setAttributeValue($r, $result['action'], 'action');
        $this->setAttributeValue($r, $result['predicted_value'], 'predictedValue');
        $this->setAttributeValue($r, $result['predicted_date'], 'predictedDate');
        $this->setAttributeValue($r, $result['recurrent'], 'recurrent');
        $this->setAttributeValue($r, $result['observation'], 'observation');

        $this->identityMap[$r] = $id;
        return $r;        

    }

    public function delete(C3op_Projects_Outlay $a)
    {
        if (!isset($this->identityMap[$a])) {
            throw new C3op_Projects_OutlayMapperException('Object has no ID, cannot delete.');
        }
        $this->db->exec(
            sprintf(
                'DELETE FROM projects_outlays WHERE id = %d;',
                $this->identityMap[$a]
            )
        );
        unset($this->identityMap[$a]);
    }
    

    private function setAttributeValue(C3op_Projects_Outlay $a, $fieldValue, $attributeName)
    {
        $attribute = new ReflectionProperty($a, $attributeName);
        $attribute->setAccessible(TRUE);
        $attribute->setValue($a, $fieldValue);
    }
    
     public function getAllOutlaysForHumanResource(C3op_Projects_HumanResource $h) {
        $result = array();
            foreach ($this->db->query(
                    sprintf('SELECT id FROM projects_outlays WHERE human_resource = %d;', $h->GetId())) as $row) {
            $result[] = $row['id'];
        }        
        return $result;
    }


    
}