<?php

class C3op_Projects_MaterialSupplyDismissal {

    public function ContactDismiss(C3op_Projects_Action $action,
                                   C3op_Projects_MaterialSupply $materialSupply,
                                   C3op_Projects_MaterialSupplyMapper $materialSupplyMapper)
    {

        if ($materialSupply->GetLinkage() > 0) {
            $materialSupply->SetLinkage(0);
            $materialSupplyMapper->update($materialSupply);

            $this->LogDismiss($action);
        }

    }

    private function LogDismiss(C3op_Projects_Action $action, C3op_Projects_MaterialSupply $materialSupply)
    {
        $logger = new C3op_Projects_EventLogger();
        $msg = $materialSupply->getLinkage();
        $logger->LogActionEvent($action, C3op_Projects_ActionEventConstants::EVENT_DISMISS_RESOURCE, $msg);
    }



}

