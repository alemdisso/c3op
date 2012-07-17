<?php

class C3op_Projects_ReceiptRejection {
    private $db;
    
    public function RejectReceipt(C3op_Projects_Action $action, C3op_Projects_ActionMapper $mapper)
    {
        
        $action->SetStatus(C3op_Projects_ActionStatusConstants::STATUS_IN_EXECUTION);            
        //$action->SetRealFinishDate("0000-00-00");
        $mapper->update($action);

        $this->LogRejection($action);

        
    }
    
    private function LogRejection(C3op_Projects_Action $action)
    {
        $logger = new C3op_Projects_EventLogger();
        $logger->LogActionEvent($action, C3op_Projects_ActionEventConstants::EVENT_REJECT_RECEIPT);
    }
    
}

