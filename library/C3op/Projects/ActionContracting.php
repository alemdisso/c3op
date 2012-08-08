<?php

class C3op_Projects_ActionContracting {
    
    private $action;
    private $actionMapper;

    public function __construct(C3op_Projects_Action $action, C3op_Projects_ActionMapper $actionMapper) 
    {
    
        $this->action = $action;
        $this->actionMapper = $actionMapper;
    }
    
    public function isContracted()
    {
        $result = $this->actionMapper->getContractedHumanResources($this->action);
        if (count($result)) {
            return true;
        } else {
            return false;
        }
    }
}
