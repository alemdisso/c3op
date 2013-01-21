<?php

class C3op_Resources_TeamMemberDismissal {

    public function ContactDismiss(C3op_Projects_Action $action,
                                   C3op_Resources_TeamMember $teamMember,
                                   C3op_Resources_TeamMemberMapper $teamMemberMapper)
    {

        if ($teamMember->GetLinkage() > 0) {
            $teamMember->SetLinkage(0);
            $teamMemberMapper->update($teamMember);

            $this->LogDismiss($action);
        }

    }

    private function LogDismiss(C3op_Projects_Action $action, C3op_Resources_TeamMember $teamMember)
    {
        $logger = new C3op_Projects_EventLogger();
        $msg = $teamMember->getLinkage();
        $logger->LogActionEvent($action, C3op_Projects_ActionEventConstants::EVENT_DISMISS_RESOURCE, $msg);
    }



}

