<?php

class C3op_Resources_TeamMemberContracting {

    public function teamMemberContract(C3op_Projects_Action $action,
                                   C3op_Resources_TeamMember $teamMember,
                                   C3op_Resources_TeamMemberMapper $teamMemberMapper)
    {

        if (($teamMember->GetLinkage() > 0)
                && ($teamMember->GetStatus() == C3op_Resources_TeamMemberStatusConstants::STATUS_FORESEEN)){
            $teamMember->SetStatus(C3op_Resources_TeamMemberStatusConstants::STATUS_CONTRACTED);
            $teamMemberMapper->update($teamMember);

            $this->logContracting($action, $teamMember);
        }

    }

    private function logContracting(C3op_Projects_Action $action, C3op_Resources_TeamMember $teamMember)
    {
        $logger = new C3op_Projects_EventLogger();
        $msg = $teamMember->getLinkage();
        $logger->LogActionEvent($action, C3op_Projects_ActionEventConstants::EVENT_CONTRACT_RESOURCE, $msg);
    }



}

