<?php

class C3op_Projects_TeamMemberHasCredit {

    private $teamMember;
    private $mapper;

    function __construct(C3op_Projects_TeamMember $teamMember, C3op_Projects_TeamMemberMapper $mapper)
    {
            $this->teamMember = $teamMember;
            $this->mapper = $mapper;
    }

    public function hasCredit()
    {
        $budget = $this->teamMember->getValue();
        $payed = $this->mapper->getSumOfPayedOutlays($this->teamMember);
        $provided = $this->mapper->getSumOfProvidedButNotPayedOutlays($this->teamMember);

        if ($budget > ($payed + $provided))
        {
            return true;
        } else {
            return false;

        }
    }


}

