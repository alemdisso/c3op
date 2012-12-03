<?php

class C3op_Register_ContactCanBeRemoved {

    private $contact;
    private $contactMapper;

    public function __construct(C3op_Register_Contact $contact, C3op_Register_ContactMapper $contactMapper)
    {
        $this->contact = $contact;
        $this->contactMapper = $contactMapper;
    }

    public function CanBeRemoved()
    {
        if ($this->contactWasATeamMember()) {
            return false;
        } else {
            return true;
        }
    }

    private function contactWasATeamMember()
    {
        $result = $this->contactMapper->getAllActionsWithAContactAsTeamMember($this->contact);
        if ((is_array($result)) && (count($result) > 0)) {
            return true;
        }
        return false;


    }

}
