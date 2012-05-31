<?php

class C3op_Projects_ReceivingMapperBase
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
        foreach ($this->db->query('SELECT id FROM projects_receivings;') as $row) {
            $result[] = $row['id'];
        }        
        return $result;
    }
    
    public function insert(C3op_Projects_Receiving $new)
    {
        $data = array(
            'name' => $new->GetName(),
            'project' => $new->GetProject(),
            'predicted_date' => $new->GetPredictedDate(),
            'real_date' => $new->GetRealDate(),
            'predicted_value' => $new->GetPredictedValue(),
            'real_value' => $new->GetRealValue(),
            );
        $this->db->insert('projects_receivings', $data);
        $new->SetId((int)$this->db->lastInsertId());
        $this->identityMap[$new] = $new->GetId();
        
    }
    
    public function update(C3op_Projects_Receiving $r)
    {
        if (!isset($this->identityMap[$r])) {
            throw new C3op_Projects_ReceivingMapperException('Object has no ID, cannot update.');
        }
        
        $this->db->exec(
            sprintf(
                'UPDATE projects_receivings SET name = \'%s\', project = %d, predicted_date = \'%s\', real_date = \'%s\', predicted_value = %.2f , real_value = %.2f WHERE id = %d;',
                $r->GetName(),
                $r->GetProject(),
                $r->GetPredictedDate(),
                $r->GetRealDate(),
                $r->GetPredictedValue(),
                $r->GetRealValue(),
                $this->identityMap[$r]
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
                'SELECT name, project, predicted_date, real_date, predicted_value, real_value FROM projects_receivings WHERE id = %d;',
                $id
            )
        );
        if (empty($result)) {
            throw new C3op_Projects_ReceivingMapperException(sprintf('There is no receiving with id #%d.', $id));
        }
        $project = $result['project'];
        
        $r = new C3op_Projects_Receiving($result['project'], $result['predicted_date'], $result['predicted_value'], $id);
        $this->setAttributeValue($r, $id, 'id');
        $this->setAttributeValue($r, $result['name'], 'name');
        $this->setAttributeValue($r, $result['real_date'], 'realDate');
        $this->setAttributeValue($r, $result['real_value'], 'realValue');

        $this->identityMap[$r] = $id;
        return $r;        

    }

    public function delete(C3op_Projects_Receiving $a)
    {
        if (!isset($this->identityMap[$a])) {
            throw new C3op_Projects_ReceivingMapperException('Object has no ID, cannot delete.');
        }
        $this->db->exec(
            sprintf(
                'DELETE FROM projects_receivings WHERE id = %d;',
                $this->identityMap[$a]
            )
        );
        unset($this->identityMap[$a]);
    }
    

    private function setAttributeValue(C3op_Projects_Receiving $a, $fieldValue, $attributeName)
    {
        $attribute = new ReflectionProperty($a, $attributeName);
        $attribute->setAccessible(TRUE);
        $attribute->setValue($a, $fieldValue);
    }
    
    
}