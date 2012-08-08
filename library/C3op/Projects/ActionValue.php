<?php

class C3op_Projects_ActionValue {
    
    private $action;
    private $actionMapper;

    public function __construct(C3op_Projects_Action $action, C3op_Projects_ActionMapper $actionMapper) 
    {    
        $this->action = $action;
        $this->actionMapper = $actionMapper;
    }
    
    public function totalValue()
    {
        return $this->actionMapper->getContractedValueForActionTree($this->action);
    }
}
