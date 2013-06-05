<?php

class C3op_Resources_ResponsibleInstitutionInfo {

    private $responsible;
    private $mapper;
    private $institutionMapper;
    private $db;

    function __construct(C3op_Resources_Responsible $responsible, C3op_Resources_ResponsibleMapper $mapper, $db)
    {
            $this->responsible = $responsible;
            $this->mapper = $mapper;
            $this->db = $db;
    }

    public function institutionShortName()
    {
        $institutionShortName = _("#(undefined)");
        $institutionId = $this->responsible->GetInstitution();;
        if ($institutionId > 0) {
            $this->initInstitutionMapper();
            $responsibleInstitution = $this->institutionMapper->findById($institutionId);
            $institutionShortName = $responsibleInstitution->GetName();
        }
        return $institutionShortName;
    }

    private function initInstitutionMapper()
    {
        if (!isset($this->institutionMapper)) {
             $this->institutionMapper = new C3op_Register_InstitutionMapper($this->db);
        }
    }

}
