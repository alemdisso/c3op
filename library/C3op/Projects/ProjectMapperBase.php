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
            'client' => $new->getClient(),
            'our_responsible' => $new->GetOurResponsible(),
            'responsible_at_client' => $new->GetResponsibleAtClient(),
            'date_begin' => $new->GetDateBegin(),
            'date_finish' => $new->GetDateFinish(),
            'status' => $new->GetStatus(),
            'value' => $new->GetValue(),
            'contract_nature' => $new->GetContractNature(),
            'area_activity' => $new->GetAreaActivity(),
            'overhead' => $new->GetOverhead(),
            'management_fee' => $new->GetManagementFee(),
            'object' => $new->GetObject(),
            'summary' => $new->GetSummary(),
            'observation' => $new->GetObservation(),
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
                'UPDATE projects_projects SET title = \'%s\'
                    , client = %d
                    , our_responsible = %d
                    , responsible_at_client = %d
                    , date_begin = \'%s\'
                    , date_finish = \'%s\'
                    , status = %d
                    , value = %f 
                    , contract_nature = %d
                    , area_activity = %d
                    , overhead = %f 
                    , management_fee = %f 
                    , object = \'%s\'
                    , summary = \'%s\'
                    , observation = \'%s\'
                    WHERE id = %d;',
                $p->GetTitle(),
                $p->GetClient(),
                $p->GetOurResponsible(),
                $p->GetResponsibleAtClient(),
                $p->GetDateBegin(),
                $p->GetDateFinish(),
                $p->GetStatus(),
                $p->GetValue(),
                $p->GetContractNature(),
                $p->GetAreaActivity(),
                $p->GetOverhead(),
                $p->GetManagementFee(),
                $p->GetObject(),
                $p->GetSummary(),
                $p->GetObservation(),
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
                'SELECT title
                    , client
                    , our_responsible
                    , responsible_at_client
                    , date_begin
                    , date_finish
                    , status
                    , value
                    , contract_nature
                    , area_activity
                    , overhead
                    , management_fee
                    , object
                    , summary
                    , observation FROM projects_projects WHERE id = %d;',
                $id
            )
        );
        if (empty($result)) {
            throw new C3op_Projects_ProjectMapperException(sprintf('There is no project with id #%d.', $id));
        }
        $p = new C3op_Projects_Project();
        
        $this->setAttributeValue($p, $id, 'id');
        $this->setAttributeValue($p, $result['title'], 'title');
        $this->setAttributeValue($p, $result['client'], 'client');
        $this->setAttributeValue($p, $result['our_responsible'], 'ourResponsible');
        $this->setAttributeValue($p, $result['responsible_at_client'], 'responsibleAtClient');
        $this->setAttributeValue($p, $result['date_begin'], 'dateBegin');
        $this->setAttributeValue($p, $result['date_finish'], 'dateFinish');
        $this->setAttributeValue($p, $result['status'], 'status');
        $this->setAttributeValue($p, $result['value'], 'value');
        $this->setAttributeValue($p, $result['contract_nature'], 'contractNature');
        $this->setAttributeValue($p, $result['area_activity'], 'areaActivity');
        $this->setAttributeValue($p, $result['overhead'], 'overhead');
        $this->setAttributeValue($p, $result['management_fee'], 'managementFee');
        $this->setAttributeValue($p, $result['object'], 'object');
        $this->setAttributeValue($p, $result['summary'], 'summary');
        $this->setAttributeValue($p, $result['observation'], 'observation');
        
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

    public function getAllReceivings(C3op_Projects_Project $p)
    {
        $result = array();
        foreach ($this->db->query(
                sprintf(
                    'SELECT id FROM projects_receivings WHERE project = %d;',
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
 
    public function getAllProductsOf(C3op_Projects_Project $p)
    {
            return $this->getAllActionsSubordinatedTo($p, 0);
    }
 
    private function setAttributeValue(C3op_Projects_Project $p, $fieldValue, $attributeName)
    {
        $attribute = new ReflectionProperty($p, $attributeName);
        $attribute->setAccessible(TRUE);
        $attribute->setValue($p, $fieldValue);
    }

}