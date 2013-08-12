<?php

class C3op_Projects_ReceiptAcknowledgment {

    public function AcknowledgeReceipt(C3op_Projects_Action $action, C3op_Projects_ActionMapper $mapper)
    {
        if ($action->GetStatus() == C3op_Projects_ActionStatusConstants::STATUS_IN_EXECUTION) {
            $action->SetStatus(C3op_Projects_ActionStatusConstants::STATUS_RECEIVED);
            $mapper->update($action);
        }

        $this->LogAcknowledgment($action);

    }

    public function changeReceiptDate(C3op_Projects_Action $action, C3op_Projects_ActionMapper $mapper, $newDate, $observation, C3op_Auth_User $user)
    {
        $mapper->updateLastReceiptDate($action, $newDate, $observation, $user->getId());
        //$this->LogChangeReceiptDate($action, $observation);


    }

    private function LogAcknowledgment(C3op_Projects_Action $action)
    {
        $logger = new C3op_Projects_EventLogger();
        $logger->LogActionEvent($action, C3op_Projects_ActionEventConstants::EVENT_ACKNOWLEDGE_RECEIPT);
    }


    private function LogChangeReceiptDate(C3op_Projects_Action $action, $observation)
    {
        $logger = new C3op_Projects_EventLogger();
        $logger->LogActionEvent($action, C3op_Projects_ActionEventConstants::EVENT_CHANGE_RECEIPT, $observation);
    }

}

