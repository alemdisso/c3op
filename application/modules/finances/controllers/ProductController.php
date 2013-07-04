<?php

class Finances_ProductController extends Zend_Controller_Action
{
    private $actionMapper;
    private $responsibleMapper;
    private $projectMapper;
    private $receivableMapper;
    private $contactMapper;
    private $institutionMapper;
    private $db;

    public function preDispatch()
    {
        try {
            $checker = new C3op_Access_PrivilegeChecker();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function init()
    {
        $this->db = Zend_Registry::get('db');
    }

    public function detailAction()
    {
        $pageData = array();

        $this->initActionMapper();
        $this->initProjectMapper();
        $this->initContactMapper();
        $this->initInstitutionMapper();

        $productToBeDetailed =  $this->initActionWithCheckedId($this->actionMapper);
//        $projectToBeDetailed = $this->projectMapper->findById($actionToBeDetailed->getProject());
        $messageToShow = $this->_helper->flashMessenger->getMessages();

        $header = new C3op_Projects_ActionHeader($this->db, $productToBeDetailed, $this->actionMapper);
        $actionHeader = $header->fetch();

        if ($actionHeader['notAProduct'] ) {
            throw new C3op_Projects_ActionException("This action is not a product.");
        }

        // responsiblesList
        //   * responsibleInfo
        //      id
        //      contactId
        //      linkageId
        //      name
        //      description
        //      value
        //      contractingStatusLabel
        //      canContractFlag
        //      canRemoveResponsible
        //      canProvideOutlay

        $responsiblesList = $this->getResponsiblesList($productToBeDetailed);

        // materialSupplyList
        //   * materialSupplyInfo
        //      id
        //      name
        //      description
        //      value
        //      contractingStatusLabel
        //      canContractFlag
        //      canRemoveMaterialSupply
        //      canProvideOutlay

        $materialSuppliesList = $this->getMaterialSuppliesList($productToBeDetailed);

        $pageData = array(
            'messageToShow'        => $messageToShow,
            'actionHeader'         => $actionHeader,
            'responsiblesList'     => $responsiblesList,
            'materialSuppliesList' => $materialSuppliesList,
        );

        $this->view->pageData = $pageData;

    }



    private function initActionWithCheckedId(C3op_Projects_ActionMapper $mapper)
    {
        return $mapper->findById($this->checkIdFromGet());
    }

    private function checkIdFromGet()
    {
        $data = $this->_request->getParams();
        $filters = array(
            'id' => new Zend_Filter_Alnum(),
        );
        $validators = array(
            'id' => array('Digits', new Zend_Validate_GreaterThan(0)),
        );
        $input = new Zend_Filter_Input($filters, $validators, $data);
        if ($input->isValid()) {
            $id = $input->id;
            return $id;
        }
        throw new C3op_Projects_ActionException("Invalid Action Id from Get");

    }

    private function initProjectMapper()
    {
        if (!isset($this->projectMapper)) {
             $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
        }
    }

    private function initActionMapper()
    {
        if (!isset($this->actionMapper)) {
            $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
        }
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

    private function initMaterialSupplyMapper()
    {
        if (!isset($this->materialSupplyMapper)) {
            $this->materialSupplyMapper = new C3op_Resources_MaterialSupplyMapper($this->db);
        }
    }

    private function initReceivableMapper()
    {
        if (!isset($this->receivableMapper)) {
            $this->receivableMapper = new C3op_Finances_ReceivableMapper($this->db);
        }
    }


     private function getResponsiblesList(C3op_Projects_Action $action)
    {
        // responsiblesList
        //   * responsibleInfo
        //      id
        //      contactId
        //      linkageId
        //      name
        //      contractingStatusLabel
        //      actionId
        //      actionTitle
        //      provided budget
        //      contracted value
        //      contractingStatusLabel
        //      canContractFlag
        //      canRemoveResponsible
        //      canEditResource
        //      canProvideOutlay
        $this->initActionMapper();
        $this->initResponsibleMapper();

        $responsiblesList = array();
        //$responsiblesIdsList = $this->responsibleMapper->getAllResponsiblesOnAction($action);
        $responsiblesIdsList = $this->actionMapper->getAllUniqueResponsiblesContractedOrPredictedUnderAction($action);

        foreach ($responsiblesIdsList as $responsibleId) {
            $loopResponsible = $this->responsibleMapper->findById($responsibleId);
            $responsibleAction = $this->actionMapper->findById($loopResponsible->getAction());

            $responsible = new C3op_Projects_ActionResponsible($responsibleAction, $this->actionMapper, $this->db);

            $responsibleData = $responsible->fetch();

            $responsibleActionTitle = $responsibleAction->getTitle();

            $actionStatus = $responsibleAction->getStatus();
            $statusTypes = new C3op_Projects_ActionStatusTypes();
            $actionStatusLabel = $this->view->translate($statusTypes->TitleForType($actionStatus));




            $institutionId = 0;
            $finder = new C3op_Resources_ResponsibleContactInfo($loopResponsible, $this->responsibleMapper, $this->db);
            $contactLabel = $finder->contactName();
            $contactId = $loopResponsible->getContact();
            $responsibleType = $loopResponsible->getType();


            if ($loopResponsible->getType() == C3op_Resources_ResponsibleTypeConstants::TYPE_TEAM_MEMBER) {
                $responsibleLabel = $contactLabel;
                $personal = true;
            } else {
                $finder = new C3op_Resources_ResponsibleInstitutionInfo($loopResponsible, $this->responsibleMapper, $this->db);
                $responsibleLabel = $finder->institutionShortName();
                $institutionId = $loopResponsible->getInstitution();
                if ($contactId > 0) {
                    $responsibleLabel = "$responsibleLabel ($contactLabel)";
                }
                $personal = false;
            }






            $statusLabel = $this->view->translate($responsibleData['statusLabel']);
            $canContract = $responsibleData['canContract'];
            $canDismiss = $responsibleData['canDismiss'];
            $canProvideOutlay = $responsibleData['canProvideOutlay'];
            $predictedValue = $responsibleData['predictedValue'];
            $contractedValue = $responsibleData['contractedValue'];

            $status = $loopResponsible->getStatus();

            $removal = new C3op_Resources_ResponsibleRemoval($loopResponsible, $this->responsibleMapper);
            if ($removal->canBeRemoved()) {
                $canRemoveResponsible = true;
                $canEditResource = true;
            } else {
                $canRemoveResponsible = false;
                $canEditResource = false;
            }

            $responsiblesList[$responsibleId] = array(
                'id'                     => $responsibleId,
                'contactId'              => $contactId,
                'institutionId'          => $institutionId,
                'name'                   => $responsibleLabel,
                'responsibleType'        => $responsibleType,
                'personal'               => $personal,
                'responsibleActionId'    => $loopResponsible->getAction(),
                'responsibleActionTitle' => $responsibleActionTitle,
                'actionStatusLabel'      => $actionStatusLabel,
                'contractingStatusLabel' => $statusLabel,
                'actionBudget'           => $predictedValue,
                'contractedValue'        => $contractedValue,
                'canContractFlag'        => $canContract,
                'canDismissFlag'         => $canDismiss,
                'canRemoveResponsible'   => $canRemoveResponsible,
                'canEditResource'        => $canEditResource,
                'canProvideOutlay'       => $canProvideOutlay,

            );
        }

        return $responsiblesList;

    }


     private function getMaterialSuppliesList(C3op_Projects_Action $action)
    {
        // materialSupplyList
        //   * materialSupplyInfo
        //      id
        //      name
        //      description
        //      value
        //      contractingStatusLabel
        //      canContractFlag
        //      canRemoveMaterialSupply
        //      canProvideOutlay

        $this->initMaterialSupplyMapper();

        $materialSuppliesList = array();
        $materialSuppliesIdsList = $this->materialSupplyMapper->getAllMaterialSuppliesOnAction($action);

        foreach ($materialSuppliesIdsList as $materialSupplyId) {
            $theMaterialSupply = $this->materialSupplyMapper->findById($materialSupplyId);
            $currencyDisplay = new  C3op_Util_CurrencyDisplay();
            $currencyValue = $currencyDisplay->FormatCurrency($theMaterialSupply->GetTotalValue());
            //$totalValueExistentOutlays = $this->calculateTotalValueExistentOutlays($theMaterialSupply);
            $totalValueExistentOutlays = "???";

            $descriptionMessage = $theMaterialSupply->GetDescription();

            $institutionId = $theMaterialSupply->GetInstitution();
            $institutionName = $this->view->translate("(#not defined)");
            if ($institutionId > 0) {
                $this->initContactMapper();
                $this->initInstitutionMapper();
                $institutionService = $this->institutionMapper->findById($institutionId);
                $institutionName = $institutionService->GetName();
            }


            $status = $theMaterialSupply->getStatus();
            $statusTypes = new C3op_Resources_MaterialSupplyStatusTypes();
            $statusLabel = $this->view->translate($statusTypes->TitleForType($status));

            if ($status == C3op_Resources_MaterialSupplyStatusConstants::STATUS_FORESEEN) {
                $canContract = true;
            } else {
                $canContract = false;
            }

            if ($status == C3op_Resources_MaterialSupplyStatusConstants::STATUS_CONTRACTED) {
                $doesIt = new C3op_Resources_MaterialSupplyHasCredit($theMaterialSupply, $this->materialSupplyMapper);
                if ($doesIt->hasCredit()) {
                    $canProvideOutlay = true;
                } else {
                    $canProvideOutlay = false;
                }
            } else {
                $canProvideOutlay = false;
            }
           $removal = new C3op_Resources_MaterialSupplyRemoval($theMaterialSupply, $this->materialSupplyMapper);

            if ($removal->canBeRemoved()) {
                $canRemoveMaterialSupply = true;
            } else {
                $canRemoveMaterialSupply = false;
            }




            $materialSuppliesList[$materialSupplyId] = array(
                'id'                      => $materialSupplyId,
                'name'                    => $institutionName,
                'description'             => $descriptionMessage,
                'value'                   => $currencyValue,
                'contractingStatusLabel'  => $statusLabel,
                'canContractFlag'         => $canContract,
                'canRemoveMaterialSupply' => $canRemoveMaterialSupply,
                'canProvideOutlay'        => $canProvideOutlay,

            );
        }

        return $materialSuppliesList;

    }

    private function fillSubordinatedToDataFrom($projectId, $actionId=0)
    {
            if (!isset($this->actionMapper)) {
                $this->initActionMapper();
            }

            if ($actionId > 0) {
                $actionToBePopulated = $this->actionMapper->findById($actionId);
                $parentActionId = $actionToBePopulated->GetSubordinatedTo();
                $actionsList = $this->actionMapper->getPossibleSubordination($actionToBePopulated);
            } else {
                if (!isset($this->projectMapper)) {
                    $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
                }
                $theProject = $this->projectMapper->findById($projectId);
                $actionsList = $this->projectMapper->getAllActions($theProject);
            }

            $data = array();
            foreach ($actionsList as $k => $id) {
                $loopAction = $this->actionMapper->findById($id);
                $data[] = array('id' => $id, 'title' => $loopAction->getTitle());
            }

            return $data;

    }


}