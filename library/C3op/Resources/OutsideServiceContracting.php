<?php

class C3op_Resources_OutsideServiceContracting {

    public function outsideServiceContract(C3op_Projects_Action $action,
                                   C3op_Resources_OutsideService $outsideService,
                                   C3op_Resources_OutsideServiceMapper $outsideServiceMapper)
    {

        if (($outsideService->GetLinkage() > 0)
                && ($outsideService->GetStatus() == C3op_Resources_OutsideServiceStatusConstants::STATUS_FORESEEN)){
            $outsideService->SetStatus(C3op_Resources_OutsideServiceStatusConstants::STATUS_CONTRACTED);
            $outsideServiceMapper->update($outsideService);

            $this->logContracting($action, $outsideService);
        }

    }

    private function logContracting(C3op_Projects_Action $action, C3op_Resources_OutsideService $outsideService)
    {
        $logger = new C3op_Projects_EventLogger();
        $msg = $outsideService->getLinkage();
        $logger->LogActionEvent($action, C3op_Projects_ActionEventConstants::EVENT_CONTRACT_RESOURCE, $msg);
    }



}

