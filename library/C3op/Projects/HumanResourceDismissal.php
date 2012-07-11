<?php

class C3op_Projects_HumanResourceDismissal {
    
    public function ContactDismiss(C3op_Projects_Action $action, 
                                   C3op_Projects_HumanResource $humanResource, 
                                   C3op_Projects_HumanResourceMapper $humanResourceMapper)
    {

        if ($humanResource->GetContact() > 0) {
            $humanResource->SetContact(0);
            $humanResourceMapper->update($humanResource);

            $this->LogDismiss($action);
        }
    
    }
    
    private function LogDismiss(C3op_Projects_Action $action)
    {
        $logger = new C3op_Projects_EventLogger();
        $logger->LogActionEvent($action, C3op_Projects_ActionEventConstants::EVENT_DISMISS_RESOURCE);
    }
    
    
    
}

