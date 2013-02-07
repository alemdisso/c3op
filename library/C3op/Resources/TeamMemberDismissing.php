<?php

class C3op_Resources_TeamMemberDismissing {

    public function teamMemberDismiss(C3op_Projects_Action $action,
                                   C3op_Resources_TeamMember $teamMember,
                                   C3op_Resources_TeamMemberMapper $teamMemberMapper,
                                    $observation)
    {



        if (($observation != "") && ($teamMember->GetLinkage() > 0)
                && ($teamMember->GetStatus() == C3op_Resources_TeamMemberStatusConstants::STATUS_CONTRACTED)){
            $teamMember->SetStatus(C3op_Resources_TeamMemberStatusConstants::STATUS_DISMISSED);
            $teamMemberMapper->update($teamMember);

            $this->logDismissing($action, $teamMember, $observation);
        }

    }

    private function logDismissing(C3op_Projects_Action $action, C3op_Resources_TeamMember $teamMember, $observation)
    {
        $logger = new C3op_Projects_EventLogger();
        $msg = $teamMember->getLinkage();
        $logger->LogActionEvent($action, C3op_Projects_ActionEventConstants::EVENT_DISMISS_RESOURCE, "$msg|$observation");
    }



}

