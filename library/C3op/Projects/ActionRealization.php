<?php

class C3op_Projects_ActionRealization {
    
    public function init()
    {
    }
    
    public function ConfirmRealization(C3op_Projects_Action $action, C3op_Projects_ActionMapper $mapper)
    {
        if (!$action->GetDone()) {
            $action->SetDone(1);
            $action->SetStatus(C3op_Projects_ActionStatusConstants::STATUS_DONE);
            $action->SetRealFinishDate(date("Y-m-d"));
            $mapper->update($action);
        }
        
        $this->LogRealization($action);

    }
    
    private function LogRealization(C3op_Projects_Action $action)
    {
        $logger = new C3op_Projects_EventLogger();
        $logger->LogActionEvent($action, C3op_Projects_ActionEventConstants::EVENT_CONFIRM_REALIZATION);
    }
    
}

