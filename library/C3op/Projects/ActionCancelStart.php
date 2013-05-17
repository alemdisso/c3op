<?php

class C3op_Projects_ActionCancelStart {

    public function __construct(C3op_Projects_Action $action)
    {
        $this->LogAcknowledgment($action);
    }

    private function LogAcknowledgment(C3op_Projects_Action $action)
    {
        $logger = new C3op_Projects_EventLogger();
        $logger->LogActionEvent($action,
                C3op_Projects_ActionEventConstants::EVENT_BEGIN_CANCEL_AUTO_START);
    }
}


