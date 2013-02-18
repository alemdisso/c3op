<?php

class C3op_Resources_TeamMemberHasCredit {

    private $teamMember;
    private $mapper;

    function __construct(C3op_Resources_TeamMember $teamMember, C3op_Resources_TeamMemberMapper $mapper)
    {
            $this->teamMember = $teamMember;
            $this->mapper = $mapper;
    }

    public function hasCreditToPay()
    {
        if ($this->actionHasBeenApproved()) {
            $budget = $this->teamMember->getValue();
            $payed = $this->mapper->getSumOfPayedOutlays($this->teamMember);
            $provided = $this->mapper->getSumOfProvidedButNotPayedOutlays($this->teamMember);

            if ($budget > ($payed))
            {
                return true;
            } else {
                return false;

            }
        } else {
            return false;
        }
    }

    public function hasCreditToProvide()
    {
        $budget = $this->teamMember->getValue();
        $payed = $this->mapper->getSumOfPayedOutlays($this->teamMember);
        $provided = $this->mapper->getSumOfProvidedOutlays($this->teamMember);

        //die("$budget > $provided");
        if ($budget > $provided)
        {
            return true;
        } else {
            return false;

        }
    }

    public function nextOutlayToPay()
    {

        if ($this->hasCreditToPay()) {
            $result = $this->mapper->getNextOutlayToPayTo($this->teamMember);
            return result;
        } else {
            return null;
        }

    }

    private function actionHasBeenApproved()
    {
        $actionMapper = new C3op_Projects_ActionMapper;
        $action = $actionMapper->findById($this->teamMember->getAction());

        if (($action->getStatus() == C3op_Projects_ActionStatusConstants::STATUS_DONE)
                || ($action->getStatus() == C3op_Projects_ActionStatusConstants::STATUS_DELIVERED)
                || ($action->getStatus() == C3op_Projects_ActionStatusConstants::STATUS_COMPLETE)) {
            return true;
        } else {
            return false;
        }




    }


}
