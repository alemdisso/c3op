<?php

class C3op_Projects_ReceiptAcceptance {
    
    public function init()
    {
    }
    
    public function AcceptReceipt(C3op_Projects_Action $action, C3op_Projects_ActionMapper $mapper)
    {
        if ($action->GetStatus() == C3op_Projects_ActionStatusConstants::STATUS_RECEIVED) {
            $action->SetStatus(C3op_Projects_ActionStatusConstants::STATUS_DONE);
            $action->SetDone(true);
            $mapper->FetchLastReceiptDate($action);
            $action->SetRealFinishDate($action->GetReceiptDate($mapper));
            $mapper->update($action);
        }
        
        $this->LogDelivery($action);
    }
    
    private function LogDelivery(C3op_Projects_Action $action)
    {
        $logger = new C3op_Projects_EventLogger();
        $logger->LogActionEvent($action, C3op_Projects_ActionEventConstants::EVENT_CONFIRM_REALIZATION);
    }
    
}

