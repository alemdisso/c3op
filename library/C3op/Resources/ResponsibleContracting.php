<?php

class C3op_Resources_ResponsibleContracting {

    public function responsibleContract(C3op_Projects_Action $action,
                                   C3op_Resources_Responsible $responsible,
                                   C3op_Resources_ResponsibleMapper $responsibleMapper)
    {

        $type = $responsible->getType();
        $contact = $responsible->getContact();
        $institution = $responsible->getInstitution();
        if (((($type == C3op_Resources_ResponsibleTypeConstants::TYPE_TEAM_MEMBER) && ($contact > 0))
          || (($type == C3op_Resources_ResponsibleTypeConstants::TYPE_OUTSIDE_SERVICE) && ($institution > 0)))
                && ($responsible->GetStatus() == C3op_Resources_ResponsibleStatusConstants::STATUS_FORESEEN)){
            $responsible->SetStatus(C3op_Resources_ResponsibleStatusConstants::STATUS_CONTRACTED);

            $responsibleMapper->update($responsible);

            $this->logContracting($action, $responsible);
        }

    }

    private function logContracting(C3op_Projects_Action $action, C3op_Resources_Responsible $responsible)
    {
        $logger = new C3op_Projects_EventLogger();
        $msg = "contact: " . $responsible->getContact() . "| institution " . $responsible->getInstitution();
        $logger->LogActionEvent($action, C3op_Projects_ActionEventConstants::EVENT_CONTRACT_RESOURCE, $msg);
    }



}

