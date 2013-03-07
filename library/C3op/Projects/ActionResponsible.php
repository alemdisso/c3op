<?php

class C3op_Projects_ActionResponsible {

    private $action;
    private $actionMapper;
    private $teamMemberMapper;

    public function __construct(C3op_Projects_Action $action, C3op_Projects_ActionMapper $actionMapper, $db)
    {
        $this->action = $action;
        $this->actionMapper = $actionMapper;
        $this->db = $db;
    }

    public function doesItHasAResponsible()
    {
        $itDoes = false;

        $result = $this->actionMapper->getAnyTeamMemberRelatedTo($this->action);
        if (count($result) > 0) {
            $itDoes = true;
        } else {
            $result = $this->actionMapper->getAnyOutsideServiceRelatedTo($this->action);
            if (count($result) > 0) {
                $itDoes = true;
            }
        }


        return $itDoes;
    }

    public function fetch()
    {
        $result = $this->actionMapper->getAnyTeamMemberRelatedTo($this->action);
        if (count($result) > 0) {
            $id = $result[0];

            $this->initTeamMemberMapper();
            $teamMember = $this->teamMemberMapper->findById($id);
            $linkageId = $teamMember->GetLinkage();
            $contactName = _("#(undefined)");
            $contactId = 0;
            if ($linkageId > 0) {
                $this->initContactMapper();
                $this->initLinkageMapper();
                $linkageContact = $this->linkageMapper->findById($linkageId);
                $contactId = $linkageContact->GetContact();
                $contractedContact = $this->contactMapper->findById($contactId);
                $contactName = $contractedContact->GetName();
            }

            $status = $teamMember->getStatus();
            $statusTypes = new C3op_Resources_TeamMemberStatusTypes();
            $statusLabel = $statusTypes->TitleForType($status);



            $data = array(
              'hasResponsible'  => true,
              'responsibleType' => 'teamMember',
              'responsibleName' => $contactName,
              'responsibleId'   => $linkageId,
              'statusLabel'     => $statusLabel,
            );
        } else {
            $result = $this->actionMapper->getAnyOutsideServiceRelatedTo($this->action);
            if (count($result) > 0) {
                $id = $result[0];

                $this->initOutsideServiceMapper();
                $outsideService = $this->outsideServiceMapper->findById($id);

                $institutionId = $outsideService->GetInstitution();
                $institutionName = _("(#not defined)");
                if ($institutionId > 0) {
                    $this->initContactMapper();
                    $this->initInstitutionMapper();
                    $institutionService = $this->institutionMapper->findById($institutionId);
                    $institutionName = $institutionService->GetName();
                }

                $status = $outsideService->getStatus();
                $statusTypes = new C3op_Resources_OutsideServiceStatusTypes();
                $statusLabel = $statusTypes->TitleForType($status);



                $data = array(
                'hasResponsible'  => true,
                'responsibleType' => 'outsideService',
                'responsibleName' => $institutionName,
                'responsibleId'   => $institutionId,
                'statusLabel'     => $statusLabel,
                );
            } else {
                $data = array(
                'hasResponsible'  => false,
                'responsibleType' => 'none',
                'responsibleName' => _("#(unassigned)"),
                'responsibleId'   => 0,
                );
            }
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

    private function initLinkageMapper()
    {
        if (!isset($this->linkageMapper)) {
            $this->linkageMapper = new C3op_Register_LinkageMapper($this->db);
        }
    }

    private function initOutsideServiceMapper()
    {
        if (!isset($this->outsideServiceMapper)) {
            $this->outsideServiceMapper = new C3op_Resources_OutsideServiceMapper($this->db);
        }
    }

    private function initTeamMemberMapper()
    {
        if (!isset($this->teamMemberMapper)) {
            $this->teamMemberMapper = new C3op_Resources_TeamMemberMapper($this->db);
        }
    }



}


