<?php

class C3op_Resources_ResponsibleHasCredit {

    private $responsible;
    private $mapper;

    function __construct(C3op_Resources_Responsible $responsible, C3op_Resources_ResponsibleMapper $mapper)
    {
            $this->responsible = $responsible;
            $this->mapper = $mapper;
    }

    public function hasCreditToPay()
    {
        if ($this->actionHasBeenApproved()) {
            $budget = $this->responsible->getValue();
            $payed = $this->mapper->getSumOfPayedOutlays($this->responsible);
            $provided = $this->mapper->getSumOfProvidedButNotPayedOutlays($this->responsible);

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
        $budget = $this->responsible->getValue();
        $payed = $this->mapper->getSumOfPayedOutlays($this->responsible);
        $provided = $this->mapper->getSumOfProvidedOutlays($this->responsible);

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
            $result = $this->mapper->getNextOutlayToPayTo($this->responsible);
            return result;
        } else {
            return null;
        }

    }

    private function actionHasBeenApproved()
    {
        $actionMapper = new C3op_Projects_ActionMapper;
        $action = $actionMapper->findById($this->responsible->getAction());

        if (($action->getStatus() == C3op_Projects_ActionStatusConstants::STATUS_DONE)
                || ($action->getStatus() == C3op_Projects_ActionStatusConstants::STATUS_DELIVERED)
                || ($action->getStatus() == C3op_Projects_ActionStatusConstants::STATUS_COMPLETE)) {
            return true;
        } else {
            return false;
        }




    }


}
