<?php

class C3op_Projects_ActionDone {
    
    private $action;
    private $actionMapper;

    public function __construct(C3op_Projects_Action $action) 
    {
        $this->action = $action;
    }
    
    public function isDone()
    {
        $status = $this->action->GetStatus();
        if (($status == C3op_Projects_ActionStatusConstants::STATUS_DONE)
                || ($status == C3op_Projects_ActionStatusConstants::STATUS_DELIVERED)
                || ($status == C3op_Projects_ActionStatusConstants::STATUS_COMPLETE)) {
            return true;
        } else {
            return false;
        }
    }
}
