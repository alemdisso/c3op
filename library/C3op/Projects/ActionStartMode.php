<?php

class C3op_Projects_ActionStartMode {
    
    private $action;
    private $actionMapper;

    public function __construct(C3op_Projects_Action $action, C3op_Projects_ActionMapper $actionMapper) 
    {
        $this->action = $action;
        $this->actionMapper = $actionMapper;
    }
    
    public function isUnacknowledged()
    {
        $lastAcknowledgment = $this->actionMapper->GetLastAcknowledgeStartDate($this->action);
        $lastAutoStart = $this->actionMapper->GetLastAutoStartDate($this->action);
        if ($lastAcknowledgment < $lastAutoStart) {
            return true;
        } else {
            return false;
        }
    }
}
