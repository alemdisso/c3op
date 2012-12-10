<?php

class C3op_Register_ContactRemoval {

    private $contact;
    private $contactMapper;

    public function __construct(C3op_Register_Contact $contact, C3op_Register_ContactMapper $contactMapper)
    {
        $this->contact = $contact;
        $this->contactMapper = $contactMapper;
    }

    public function canBeRemoved()
    {
        if ($this->contactWasATeamMember()) {
            return false;
        } else {
            return true;
        }
    }

    public function remove()
    {
        //check if can really remove
        if ($this->canBeRemoved()) {

            // remove linkages
            $linkages = $this->contactMapper->getAllLinkages($this->contact);
            $linkageMapper = new C3op_Register_LinkageMapper();
            foreach($linkages as $linkageId) {
                $loopLinkage = $linkageMapper->findById($linkageId);
                $linkageMapper->delete($loopLinkage);
            }

            // delete contact
            $this->contactMapper->delete($this->contact);

        } else {
            throw new C3op_Register_ContactException("This contact can't be removed");
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
