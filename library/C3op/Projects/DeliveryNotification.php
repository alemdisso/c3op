<?php

class C3op_Projects_DeliveryNotification {

    public function init()
    {
    }

    public function NotifyDelivery(C3op_Projects_Action $action, C3op_Projects_ActionMapper $mapper, $observation)
    {
        if ($action->GetStatus() == C3op_Projects_ActionStatusConstants::STATUS_DONE) {
            $action->SetStatus(C3op_Projects_ActionStatusConstants::STATUS_DELIVERED);
            //$action->SetDone(true);
//            $mapper->FetchLastReceiptDate($action);
//            $action->SetRealFinishDate($action->GetReceiptDate($mapper));
            $mapper->update($action);
        }

        $this->LogDelivery($action, $observation);
    }

    private function LogDelivery(C3op_Projects_Action $action, $observation)
    {
        $logger = new C3op_Projects_EventLogger();
        $logger->LogActionEvent($action, C3op_Projects_ActionEventConstants::EVENT_CONFIRM_REALIZATION, $observation);
    }

}

