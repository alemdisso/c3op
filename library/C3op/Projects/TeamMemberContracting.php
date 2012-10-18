<?php

class C3op_Projects_TeamMemberContracting {

    public static function ContactContract(C3op_Projects_Action $action,
                                   C3op_Projects_TeamMember $teamMember,
                                   C3op_Projects_TeamMemberMapper $teamMemberMapper)
    {

        if (($teamMember->GetContact() > 0)
                && ($teamMember->GetStatus() == C3op_Projects_TeamMemberStatusConstants::STATUS_FORESEEN)){
            $teamMember->SetStatus(C3op_Projects_TeamMemberStatusConstants::STATUS_CONTRACTED);
            $teamMemberMapper->update($teamMember);

            self::LogContracting($action);
        }

    }

    private function LogContracting(C3op_Projects_Action $action)
    {
        $logger = new C3op_Projects_EventLogger();
        $logger->LogActionEvent($action, C3op_Projects_ActionEventConstants::EVENT_CONTRACT_RESOURCE);
    }



}

