<?php

class C3op_Resources_ResponsibleDismissing {

    public function responsibleDismiss(C3op_Projects_Action $action,
                                   C3op_Resources_Responsible $responsible,
                                   C3op_Resources_ResponsibleMapper $responsibleMapper,
                                    $observation)
    {



        $type = $responsible->getType();
        $contact = $responsible->getContact();
        $institution = $responsible->getInstitution();
        if (($observation != "")
             && ((($type == C3op_Resources_ResponsibleTypeConstants::TYPE_TEAM_MEMBER) && ($contact > 0))
                || (($type == C3op_Resources_ResponsibleTypeConstants::TYPE_OUTSIDE_SERVICE) && ($institution > 0)))
             && ($responsible->GetStatus() == C3op_Resources_ResponsibleStatusConstants::STATUS_CONTRACTED)){
            $responsible->SetStatus(C3op_Resources_ResponsibleStatusConstants::STATUS_DISMISSED);
            $responsibleMapper->update($responsible);

            $this->logDismissing($action, $responsible, $observation);
        }

    }

    private function logDismissing(C3op_Projects_Action $action, C3op_Resources_Responsible $responsible, $observation)
    {
        $logger = new C3op_Projects_EventLogger();
        $msg = "contact: " . $responsible->getContact() . "| institution " . $responsible->getInstitution();
        $logger->LogActionEvent($action, C3op_Projects_ActionEventConstants::EVENT_DISMISS_RESOURCE, "$msg|$observation");
    }



}

