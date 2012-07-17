<?php

class C3op_Projects_ReceiptAcknowledgment {
    
    public function AcknowledgeReceipt(C3op_Projects_Action $action, C3op_Projects_ActionMapper $mapper)
    {
        if (!$action->GetDone()) {
            $action->SetStatus(C3op_Projects_ActionStatusConstants::STATUS_RECEIVED);
            $mapper->update($action);
        }
        
        $this->LogAcknowledgment($action);

    }
    
    private function LogAcknowledgment(C3op_Projects_Action $action)
    {
        $logger = new C3op_Projects_EventLogger();
        $logger->LogActionEvent($action, C3op_Projects_ActionEventConstants::EVENT_ACKNOWLEDGE_RECEIPT);
    }
    
}

