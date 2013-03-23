<?php

class C3op_Projects_ActionResponsible {

    private $action;
    private $actionMapper;
    private $responsibleMapper;

    public function __construct(C3op_Projects_Action $action, C3op_Projects_ActionMapper $actionMapper, $db)
    {
        $this->action = $action;
        $this->actionMapper = $actionMapper;
        $this->db = $db;
    }

    public function doesItHasAResponsible()
    {
        $itDoes = false;

        $result = $this->actionMapper->getResponsibleBy($this->action);
        if (count($result) > 0) {
            $itDoes = true;
        }

        return $itDoes;
    }

    public function fetch()
    {
        $result = $this->actionMapper->getResponsibleBy($this->action);
        if (count($result) > 0) {
            $id = $result[0];

            $this->initResponsibleMapper();
            $responsible = $this->responsibleMapper->findById($id);
            $contactName = _("#(undefined)");
            $contactId = $responsible->GetContact();;
            if ($contactId > 0) {
                $this->initContactMapper();
                $responsibleContact = $this->contactMapper->findById($contactId);
                $contactName = $responsibleContact->GetName();
            }

            $institutionName = _("#(undefined)");
            $institutionId = $responsible->GetInstitution();;
            if ($institutionId > 0) {
                $this->initInstitutionMapper();
                $responsibleInstitution = $this->institutionMapper->findById($institutionId);
                $institutionName = $responsibleInstitution->GetName();
            }

            $type = $responsible->getType();

            $status = $responsible->getStatus();
            $statusTypes = new C3op_Resources_ResponsibleStatusTypes();
            $statusLabel = $statusTypes->TitleForType($status);
            $data = array(
              'hasResponsible'  => true,
              'responsibleType' => $type,
              'contactName'     => $contactName,
              'contactId'       => $contactId,
              'institutionName' => $institutionName,
              'institutionId'   => $institutionId,
              'statusLabel'     => $statusLabel,
            );
        } else {
            $data = array(
            'hasResponsible'  => false,
            'contactName'     => _("#(unassigned)"),
            'contactId'       => 0,
            'institutionName' => _("#(unassigned)"),
            'institutionId'   => 0,
            'responsibleType' => 0,
            'responsibleName' => _("#(unassigned)"),
            'responsibleId'   => 0,
            );
        }
        return $data;
    }

    private function initContactMapper()
    {
        if (!isset($this->contactMapper)) {
            $this->contactMapper = new C3op_Register_ContactMapper($this->db);
        }
    }

    private function initInstitutionMapper()
    {
        if (!isset($this->institutionMapper)) {
            $this->institutionMapper = new C3op_Register_InstitutionMapper($this->db);
        }
    }


    private function initResponsibleMapper()
    {
        if (!isset($this->responsibleMapper)) {
            $this->responsibleMapper = new C3op_Resources_ResponsibleMapper($this->db);
        }
    }



}


