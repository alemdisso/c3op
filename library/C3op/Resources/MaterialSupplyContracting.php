<?php

class C3op_Projects_MaterialSupplyContracting {

    public function materialSupplyContract(C3op_Projects_Action $action,
                                   C3op_Projects_MaterialSupply $materialSupply,
                                   C3op_Projects_MaterialSupplyMapper $materialSupplyMapper)
    {

        if (($materialSupply->GetLinkage() > 0)
                && ($materialSupply->GetStatus() == C3op_Projects_MaterialSupplyStatusConstants::STATUS_FORESEEN)){
            $materialSupply->SetStatus(C3op_Projects_MaterialSupplyStatusConstants::STATUS_CONTRACTED);
            $materialSupplyMapper->update($materialSupply);

            $this->logContracting($action, $materialSupply);
        }

    }

    private function logContracting(C3op_Projects_Action $action, C3op_Projects_MaterialSupply $materialSupply)
    {
        $logger = new C3op_Projects_EventLogger();
        $msg = $materialSupply->getLinkage();
        $logger->LogActionEvent($action, C3op_Projects_ActionEventConstants::EVENT_CONTRACT_RESOURCE, $msg);
    }



}

