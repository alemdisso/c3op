<?php

class C3op_Projects_ActionComplete {

    private $action;

    public function __construct(C3op_Projects_Action $action)
    {
        $this->action = $action;
    }

    public function isComplete()
    {
        $status = $this->action->GetStatus();
        if ($status == C3op_Projects_ActionStatusConstants::STATUS_COMPLETE) {
            return true;
        } else {
            return false;
        }
    }
}
