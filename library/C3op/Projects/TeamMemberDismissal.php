<?php

class C3op_Projects_TeamMemberDismissal {

    public function ContactDismiss(C3op_Projects_Action $action,
                                   C3op_Projects_TeamMember $teamMember,
                                   C3op_Projects_TeamMemberMapper $teamMemberMapper)
    {

        if ($teamMember->GetContact() > 0) {
            $teamMember->SetContact(0);
            $teamMemberMapper->update($teamMember);

            $this->LogDismiss($action);
        }

    }

    private function LogDismiss(C3op_Projects_Action $action)
    {
        $logger = new C3op_Projects_EventLogger();
        $logger->LogActionEvent($action, C3op_Projects_ActionEventConstants::EVENT_DISMISS_RESOURCE);
    }



}

