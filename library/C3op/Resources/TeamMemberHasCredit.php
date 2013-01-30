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
        $budget = $this->teamMember->getValue();
        $payed = $this->mapper->getSumOfPayedOutlays($this->teamMember);
        $provided = $this->mapper->getSumOfProvidedButNotPayedOutlays($this->teamMember);

        if ($budget > ($payed))
        {
            return true;
        } else {
            return false;

        }
    }

    public function hasCreditToProvide()
    {
        $budget = $this->teamMember->getValue();
        $payed = $this->mapper->getSumOfPayedOutlays($this->teamMember);
        $provided = $this->mapper->getSumOfProvidedOutlays($this->teamMember);

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


}

