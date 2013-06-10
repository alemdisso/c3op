<?php

class C3op_Projects_ActionResponsible {

    private $action;
    private $actionMapper;
    private $responsibleMapper;
    private $db;

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

            if ($type == C3op_Resources_ResponsibleTypeConstants::TYPE_TEAM_MEMBER) {
                $responsibleLabel = $contactName;

            } else {
                $responsibleLabel = "$institutionName";

            }

            $status = $responsible->getStatus();
            $statusTypes = new C3op_Resources_ResponsibleStatusTypes();
            $statusLabel = $statusTypes->TitleForType($status);

            $canContract = false;
            if (((($responsible->getType() == C3op_Resources_ResponsibleTypeConstants::TYPE_OUTSIDE_SERVICE)
                    && ($responsible->getInstitution() > 0))
                    || (($responsible->getType() == C3op_Resources_ResponsibleTypeConstants::TYPE_TEAM_MEMBER)
                    && ($responsible->getContact() > 0)))
                 && ($status == C3op_Resources_ResponsibleStatusConstants::STATUS_FORESEEN)) {

                $user = Zend_Registry::get('user');
                $acl = Zend_Registry::get('acl');
                $tester = new C3op_Access_PrivilegeTester($user, $acl, "resources", "responsible", "contract");
                if ($tester->allow()) {
                    $canContract = true;
                }
            }

            $user = Zend_Registry::get('user');
            $acl = Zend_Registry::get('acl');

            $canDefineResponsible = false;
            $tester = new C3op_Access_PrivilegeTester($user, $acl, "resources", "responsible", "create");
            if ($tester->allow()) {
                $canDefineResponsible = true;
            }


            $canDismiss = false;
            $canProvideOutlay = false;
            if ($status == C3op_Resources_ResponsibleStatusConstants::STATUS_CONTRACTED) {
                $user = Zend_Registry::get('user');
                $acl = Zend_Registry::get('acl');
                $tester = new C3op_Access_PrivilegeTester($user, $acl, "resources", "responsible", "dismiss");
                if ($tester->allow()) {
                    $canDismiss = true;
                }
                $doesIt = new C3op_Resources_ResponsibleHasCredit($responsible, $this->responsibleMapper);
                if ($doesIt->hasCreditToProvide()) {
                    $canProvideOutlay = true;
                }
            }

            $data = array(
              'hasResponsible'       => true,
              'canContract'          => $canContract,
              'canDefineResponsible' => $canDefineResponsible,
              'canDismiss'           => $canDismiss,
              'canProvideOutlay'     => $canProvideOutlay,
              'responsibleType'      => $type,
              'responsibleId'        => $responsible->getId(),
              'responsibleLabel'     => $responsibleLabel,
              'contactName'          => $contactName,
              'contactId'            => $contactId,
              'institutionName'      => $institutionName,
              'institutionId'        => $institutionId,
              'statusLabel'          => $statusLabel,
            );
        } else {
            $data = array(
            'hasResponsible'   => false,
            'contactName'      => _("#(unassigned)"),
            'contactId'        => 0,
            'institutionName'  => _("#(unassigned)"),
            'institutionId'    => 0,
            'responsibleType'  => 0,
            'responsibleLabel' => _("#(unassigned)"),
            'responsibleId'    => 0,
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


