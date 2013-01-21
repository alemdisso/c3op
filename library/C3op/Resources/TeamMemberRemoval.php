<?php

class C3op_Projects_TeamMemberRemoval {

    private $teamMember;
    private $teamMemberMapper;

    public function __construct(C3op_Projects_TeamMember $teamMember, C3op_Projects_TeamMemberMapper $teamMemberMapper)
    {
        $this->teamMember = $teamMember;
        $this->teamMemberMapper = $teamMemberMapper;
    }

    public function canBeRemoved()
    {
        $can=true;
        if ($this->teamMemberHasOutlays()) {
            $can = false;
        }

        if ($this->teamMemberWasContracted()) {
            $can = false;
        }

        return $can;

    }

    public function remove()
    {
        //check if can really remove
        if ($this->canBeRemoved()) {

            // delete teamMember
            $this->teamMemberMapper->delete($this->teamMember);

        } else {
            throw new C3op_Projects_TeamMemberException("This Team Member can't be removed");
        }



    }

    private function teamMemberHasOutlays()
    {
        $result = $this->teamMemberMapper->getAllOutlays($this->teamMember);

        if (count($result)) {
            return true;
        } else {
            return false;
        }
    }

    private function teamMemberWasContracted()
    {
        $status = $this->teamMember->getStatus();

        if (($status == C3op_Projects_TeamMemberStatusConstants::STATUS_CONTRACTED)
                || ($status == C3op_Projects_TeamMemberStatusConstants::STATUS_ACQUITTED)) {
            return true;
        } else {
            return false;
        }
    }

}
