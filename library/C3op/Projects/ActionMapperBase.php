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
            'done' => $new->GetDone(),
            'status' => $new->GetStatus(),
            'description' => $new->GetDescription(),
            'subordinated_to' => $new->GetSubordinatedTo(),
            'responsible' => $new->GetResponsible(),
            'milestone' => $new->GetMilestone(),
            'requirement_for_receiving' => $new->GetRequirementForReceiving()
            );
        $this->db->insert('projects_actions', $data);
        $new->SetId((int)$this->db->lastInsertId());
        $this->identityMap[$new] = $new->GetId();
        
        $this->insertDates($new);
        
//        $data = array(
//            'action' => $new->getId(),
//            'predicted_begin_date' => $new->GetPredictedBeginDate(),
//            'predicted_finish_date' => $new->GetPredictedFinishDate(),
//            'real_begin_date' => $new->GetRealBeginDate(),
//            'real_finish_date' => $new->GetRealFinishDate(),
//            );
//        $this->db->insert('projects_actions_dates', $data);
        
        
        
    }
    
    public function update(C3op_Projects_Action $a)
    {
        if (!isset($this->identityMap[$a])) {
            throw new C3op_Projects_ActionMapperException('Object has no ID, cannot update.');
        }

        $this->db->exec(
            sprintf(
                'UPDATE projects_actions SET title = \'%s\', project = %d, done = %d, status = %d, description = \'%s\', subordinated_to = %d, responsible = %d, milestone = %d, requirement_for_receiving = %d WHERE id = %d;',
                $a->GetTitle(),
                $a->GetProject(),
                $a->GetDone(),
                $a->GetStatus(),
                $a->GetDescription(),
                $a->GetSubordinatedTo(),
                $a->GetResponsible(),
                $a->GetMilestone(),
                $a->GetRequirementForReceiving(),
                $this->identityMap[$a]
            )
        );
        
        $this->UpdateDates($a);
        
       

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
                'SELECT title, project, done, status, description, subordinated_to, responsible, milestone, requirement_for_receiving FROM projects_actions WHERE id = %d;',
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
        $this->setAttributeValue($a, $result['done'], 'done');
        $this->setAttributeValue($a, $result['status'], 'status');
        $this->setAttributeValue($a, $result['description'], 'description');
        $this->setAttributeValue($a, $result['subordinated_to'], 'subordinatedTo');
        $this->setAttributeValue($a, $result['responsible'], 'responsible');
        $this->setAttributeValue($a, $result['milestone'], 'milestone');
        $this->setAttributeValue($a, $result['requirement_for_receiving'], 'requirementForReceiving');
        

        $this->identityMap[$a] = $id;
        
        $this->FetchDates($a);
        
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
        $this->db->exec(
            sprintf(
                'DELETE FROM projects_actions_dates WHERE action = %d;',
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
    
    private function insertDates(C3op_Projects_Action $new)
    {
        $data = array(
            'action' => $new->getId(),
            'predicted_begin_date' => $new->GetPredictedBeginDate(),
            'predicted_finish_date' => $new->GetPredictedFinishDate(),
            'real_begin_date' => $new->GetRealBeginDate(),
            'real_finish_date' => $new->GetRealFinishDate(),
            );
        $this->db->insert('projects_actions_dates', $data);
    }
    
    private function FetchDates(C3op_Projects_Action $action)
    {
        $result = $this->db->fetchRow(
            sprintf(
                'SELECT predicted_begin_date, predicted_finish_date, real_begin_date, real_finish_date FROM projects_actions_dates WHERE action = %d;',
                $action->GetId()
            )
        );
        
        if (empty($result)) {
            $this->insertDates($action);
            $this->FetchDates($action);
            return;
        }
                
        $this->setAttributeValue($action, $result['predicted_begin_date'], 'predictedBeginDate');
        $this->setAttributeValue($action, $result['predicted_finish_date'], 'predictedFinishDate');
        $this->setAttributeValue($action, $result['real_begin_date'], 'realBeginDate');
        $this->setAttributeValue($action, $result['real_finish_date'], 'realFinishDate');
    }
    
   public function FetchLastReceiptDate(C3op_Projects_Action $action)
    {
        $result = $this->db->fetchRow(
            sprintf(
                'SELECT timestamp FROM projects_actions_events WHERE action = %d ORDER BY timestamp DESC LIMIT 1;',
                $action->GetId()
            )
        );
        
        if (empty($result)) {
            $receiptDate = "0000-00-00";
        } else {
            $receiptDate = $result['timestamp'];
        }
                
        $this->setAttributeValue($action, $receiptDate, 'receiptDate');
    }
    
     private function UpdateDates(C3op_Projects_Action $action)
    {
        $this->db->exec(
        sprintf(
            'UPDATE projects_actions_dates SET predicted_begin_date = \'%s\', predicted_finish_date = \'%s\', real_begin_date = \'%s\', real_finish_date = \'%s\' WHERE action = %d;',
                $action->GetPredictedBeginDate(),
                $action->GetPredictedFinishDate(),
                $action->GetRealBeginDate(),
                $action->GetRealFinishDate(),
                $this->identityMap[$action]
            )
        );

    }
}