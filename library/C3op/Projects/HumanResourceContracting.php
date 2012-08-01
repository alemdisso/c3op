<?php

class C3op_Projects_HumanResourceContracting {
    
    public static function ContactContract(C3op_Projects_Action $action, 
                                   C3op_Projects_HumanResource $humanResource, 
                                   C3op_Projects_HumanResourceMapper $humanResourceMapper)
    {

        if (($humanResource->GetContact() > 0) 
                && ($humanResource->GetStatus() == C3op_Projects_HumanResourceStatusConstants::STATUS_FORESEEN)){
            $humanResource->SetStatus(C3op_Projects_HumanResourceStatusConstants::STATUS_CONTRACTED);
            $humanResourceMapper->update($humanResource);

            self::LogContracting($action);
        }
    
    }
    
    private function LogContracting(C3op_Projects_Action $action)
    {
        $logger = new C3op_Projects_EventLogger();
        $logger->LogActionEvent($action, C3op_Projects_ActionEventConstants::EVENT_CONTRACT_RESOURCE);
    }
    
    
    
}

