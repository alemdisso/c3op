<?php

class C3op_Projects_ActionDelivery {
    
    public function init()
    {
    }
    
    public function AcknowledgeDelivery(C3op_Projects_Action $action, C3op_Projects_ActionMapper $mapper)
    {
        if ($action->GetDone()) {
            $action->SetStatus(C3op_Projects_ActionStatusConstants::STATUS_DELIVERED);
            //$action->SetRealFinishDate(date("Y-m-d"));
            $mapper->update($action);
        }
        
        $this->LogDelivery($action);
    }
    
    private function LogDelivery(C3op_Projects_Action $action)
    {
        $logger = new C3op_Projects_EventLogger();
        $logger->LogActionEvent($action, C3op_Projects_ActionEventConstants::EVENT_ACKNOWLEDGE_DELIVERY);
    }
    
}

