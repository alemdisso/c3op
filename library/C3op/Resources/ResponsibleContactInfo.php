<?php

class C3op_Resources_ResponsibleContactInfo {

    private $responsible;
    private $mapper;
    private $contactMapper;
    private $db;

    function __construct(C3op_Resources_Responsible $responsible, C3op_Resources_ResponsibleMapper $mapper, $db)
    {
            $this->responsible = $responsible;
            $this->mapper = $mapper;
            $this->db = $db;
    }

    public function contactName()
    {
        $contactName = _("#(undefined)");
        $contactId = $this->responsible->GetContact();;
        if ($contactId > 0) {
            $this->initContactMapper();
            $responsibleContact = $this->contactMapper->findById($contactId);
            $contactName = $responsibleContact->GetName();
        }
        return $contactName;
    }

    private function initContactMapper()
    {
        if (!isset($this->contactMapper)) {
             $this->contactMapper = new C3op_Register_ContactMapper($this->db);
        }
    }

}
