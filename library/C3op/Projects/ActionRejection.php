<?php

class C3op_Projects_ActionRejection {
    private $db;
    
    public function init()
    {
        $this->db = Zend_Registry::get('db');
    }


    public function RejectDelivery(C3op_Projects_Action $action, C3op_Projects_ActionMapper $mapper)
    {
        
        if ($action->GetDone()) {
            $action->SetDone(0);
            $action->SetRealFinishDate("0000-00-00");
            $mapper->update($action);
        }

        $this->LogRejection($action);

        
    }
    
    private function LogRejection(C3op_Projects_Action $action)
    {
        $logger = new C3op_Projects_EventLogger();
        $logger->LogActionEvent($action, C3op_Projects_ActionEventConstants::EVENT_REJECT_DELIVERY);
    }
    
}

