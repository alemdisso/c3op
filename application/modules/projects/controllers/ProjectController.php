<?php

class Projects_ProjectController extends Zend_Controller_Action
{
    private $db;
    private $projectMapper;
    private $actionMapper;
    private $contractMapper;
    private $institutionMapper;
    private $contactMapper;
    private $linkageMapper;
    private $outlayMapper;
    private $receivableMapper;
    private $responsibleMapper;
    private $materialSupplyMapper;
    private $treeData;

    public function preDispatch()
    {
        try {
            $checker = new C3op_Access_PrivilegeChecker();
        } catch (Exception $e) {
            $this->_helper->getHelper('FlashMessenger')
                ->addMessage('Acesso negado');
            $this->_redirect('/projects' . $id);
        }
        $this->view->pageUri = "/projects";
        $this->view->pageTitle = "";


    }

    public function postDispatch()
    {
        $trail = new C3op_Util_Breadcrumb();
        if (isset($this->view->pageTitle)) {
            $breadcrumb = $trail->add($this->view->pageTitle, $this->view->pageUri);
        }
    }


    public function init()
    {
        $this->db = Zend_Registry::get('db');
        $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
    }

    public function amendAction()
    {
        // cria form

        $form = new C3op_Form_ProjectAmend;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/projects/project/detail/?id=' . $id);
            } else {
                //form error: populate and go back
                $form->populate($postData);
                $this->view->form = $form;
            }
        } else {
            $thisProject = $this->initProjectWithCheckedId($this->projectMapper);
            $id = $this->checkIdFromGet();
            $shortTitle = $thisProject->getShortTitle();
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $id);
//            $data = $this->_request->getParams();
//            $projectId = $data['id'];
//            $this->PopulateProjectFields($projectId, $form);

        $this->view->pageTitle = $this->view->translate("#New amendment");
        $this->view->pageUri = "/projects/project/amend/?id=" . $id;
        $this->_helper->layout()->getView()->headTitle($this->view->pageTitle);

        }
    }

    public function createAction()
    {
        // cria form
        $form = new C3op_Form_ProjectCreate;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('#The record was successfully updated.');
                $this->_redirect('/projects/project/detail/?id=' . $id);
            } else {
                //form error: populate and go back
                $form->populate($postData);
                $this->view->form = $form;
            }
        } else {
            $this->populateClientField($form);
            $this->populateOurResponsibleField($form);


            $this->populateResponsibleAtClientField($form);

            $element = $form->getElement('client');
            $element->setDescription('<a href="/register/institution/create" class="two columns button alpha omega">' . $this->view->translate("#New client") . '</a>');
            $element = $form->getElement('ourResponsible');
            $element->setDescription('<a href="/register/contact/create" class="two columns button alpha omega">' . $this->view->translate("#New responsible") . '</a>');
            $element = $form->getElement('responsibleAtClient');
            $element->setDescription('<a href="/register/contact/create" class="two columns button alpha omega">' . $this->view->translate("#New responsible") . '</a>');

            $element = $form->getElement('status');
            $element->setValue(C3op_Projects_ProjectStatusConstants::STATUS_PROSPECTING);

            $this->view->pageTitle = $this->view->translate("#Create project");
            $this->view->pageUri = "/projects/project/create";
            $this->_helper->layout()->getView()->headTitle($this->view->pageTitle);

        }
    }

    public function detailAction()
    {

        if (!isset($this->projectMapper)) {
            $this->initProjectMapper();
        }
        if (!isset($this->actionMapper)) {
            $this->initActionMapper();
        }
        $projectToBeDetailed = $this->initProjectWithCheckedId($this->projectMapper);
        $messageToShow = $this->_helper->flashMessenger->getMessages();



        //  projectHeader
        //    id
        //    projectTitle
        //    clientName
        //    areaActivity
        //    ourResponsible
        //    responsibleAtClient
        //    overhead
        //    managementFee
        //    projectValue
        //    projectDates
        //
        $projectHeader = $this->fillProjectHeaderData($projectToBeDetailed);

        // productsList
        //   * id =>
        //      productTitle
        //      predictedDate
        //      realDate
        //      status
        //      physicalProgress
        //      requirementForReceiving
        //      receivableDescription

        $productsList = array();

        $projectProducts = $this->projectMapper->getAllProducts($projectToBeDetailed);
        if (!isset($this->actionMapper)) {
            $this->initActionMapper();
        }
        if (!isset($this->receivableMapper)) {
            $this->initReceivableMapper();
        }

        foreach ($projectProducts as $id) {
            $theProduct = $this->actionMapper->findById($id);
            $productTitle = $theProduct->getTitle();


            $validator = new C3op_Util_ValidDate();

            $receivableId = $theProduct->getRequirementForReceiving();
            if ($receivableId) {
                $receivable = $this->receivableMapper->findById($receivableId);
                if ($validator->isValid($receivable->getDeliveryDate())) {
                    $deliveryDate = C3op_Util_DateDisplay::FormatDateToShow($receivable->getDeliveryDate());
                } else {
                    $deliveryDate = $this->view->translate("#(no delivery)");
                }

            } else {
                $deliveryDate = $this->view->translate("#(no delivery)");
            }


            if ($validator->isValid($theProduct->getRealFinishDate())) {
                $realDate = C3op_Util_DateDisplay::FormatDateToShow($theProduct->getRealFinishDate());
            } else {
                $realDate = $this->view->translate("#(not finished)");
            }

            if ($theProduct->getRequirementForReceiving() > 0) {
                $theReceivable = $this->receivableMapper->findById($theProduct->getRequirementForReceiving());
                $requirementForReceiving = $theReceivable->getTitle();
                $receivableId = $theProduct->getRequirementForReceiving();
                $receivableDescription = $theReceivable->getDescription();
            } else {
                $requirementForReceiving = $this->view->translate("#(not a requirement)");
                $receivableId = null;
                $receivableDescription = "";
            }

            $statusTypes = new C3op_Projects_ActionStatusTypes();
            $status = $statusTypes->TitleForType($theProduct->getStatus());

            $productsList[$id] = array(
                    'productTitle'            => $productTitle,
                    'deliveryDate'            => $deliveryDate,
                    'realDate'                => $realDate,
                    'status'                  => $this->view->translate($status),
                    'physicalProgress'        => "[ND]",
                    'receivableId'            => $receivableId,
                    'requirementForReceiving' => $requirementForReceiving,
                    'receivableDescription'   => $receivableDescription,
                );
        }

        // responsiblesList
        //   * id =>
        //      staffName
        //      actionId
        //      linkageId
        //      contactId
        //      linkageInstitutionName
        //      actionTitle
        //      staffEmail
        //      staffPhoneNumber

        $responsiblesList = array();
        if (!isset($this->responsibleMapper)) {
            $this->initResponsibleMapper();
        }
//        $projectTeam = $this->responsibleMapper->getAllUniqueResponsiblesContractedOrPredictedAt($projectToBeDetailed);
        $projectTeam = $this->responsibleMapper->getAllResponsiblesContractedOrPredictedAt($projectToBeDetailed);
        if (!isset($this->linkageMapper)) {
            $this->initLinkageMapper();
        }

//        $rolesArray = $this->projectMapper->getAllUnassignedPositionsAt($projectToBeDetailed);
//        foreach ($rolesArray as $id) {
//            $projectTeam[] = $id;
//        }


        foreach ($projectTeam as $id) {
            $loopResponsible = $this->responsibleMapper->findById($id);
            $loopAction = $this->actionMapper->findById($loopResponsible->getAction());
            $responsibleForAction = new C3op_Projects_ActionResponsible($loopAction, $this->actionMapper, $this->db);
            $responsibleData = $responsibleForAction->fetch();

            $loopActionTitle = $loopAction->getTitle();

            $completion = new C3op_Projects_ActionCompletion($loopAction);

            $differenceInDays = $completion->daysDifference();
            $finishDate = $completion->finishDate();

            $statusTypes = new C3op_Projects_ActionStatusTypes();
            $rawActionStatus = $loopAction->getStatus();
            $actionStatusLabel = $this->view->translate($statusTypes->TitleForType($rawActionStatus));

            $institutionId = 0;
            $finder = new C3op_Resources_ResponsibleContactInfo($loopResponsible, $this->responsibleMapper, $this->db);
            $contactLabel = $finder->contactName();
            $contactId = $loopResponsible->getContact();

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

            $status = $loopResponsible->getStatus();

            $removal = new C3op_Resources_ResponsibleRemoval($loopResponsible, $this->responsibleMapper);
            if ($removal->canBeRemoved()) {
                $canRemoveResponsible = true;
                $canEditResource = true;
            } else {
                $canRemoveResponsible = false;
                $canEditResource = false;
            }

            $responsiblesList[$id] = array(
                'id'                     => $id,
                'personal'               => $personal,
                'contactId'              => $contactId,
                'institutionId'          => $institutionId,
                'name'                   => $responsibleLabel,
                'responsibleActionId'    => $loopResponsible->getAction(),
                'responsibleActionTitle' => $loopActionTitle,
                'actionStatusLabel'      => $actionStatusLabel,
                'contractingStatusLabel' => $statusLabel,
                'finishDate'             => $finishDate,
                'differenceInDays'       => $differenceInDays,
                'canContractFlag'        => $canContract,
                'canDismissFlag'         => $canDismiss,
                'canRemoveResponsible'   => $canRemoveResponsible,
                'canEditResource'        => $canEditResource,
                'canProvideOutlay'       => $canProvideOutlay,


            );

        }


        $materialSuppliesList = $this->getMaterialSuppliesList($projectToBeDetailed);


        $objTree = new C3op_Projects_ProjectTree();
        $tree = $objTree->retrieveTree($projectToBeDetailed, $this->projectMapper, $this->actionMapper);

        $this->treeData = array();
        $this->fillDataTree($tree);

        $actionTreeList = $this->treeData;

        // detailsData
        //   object
        $detailsData = array(
          'object'      => $projectToBeDetailed->GetObject(),
          'summary'     => $projectToBeDetailed->GetSummary(),
          'observation' => $projectToBeDetailed->GetObservation(),
        );


        $pageData = array(
            'projectHeader'        => $projectHeader,
            'messageToShow'        => $messageToShow,
            'productsList'         => $productsList,
            'actionsTree'          => $actionTreeList,
            'responsiblesList'      => $responsiblesList,
            'materialSuppliesList' => $materialSuppliesList,
            'detailsData'          => $detailsData,

        );
        $this->view->pageData = $pageData;
        $this->view->pageTitle = $this->view->translate("#Project") . " " . $projectHeader['projectTitle'];
        $this->view->pageUri = "/projects/project/detail/?id=" . $projectHeader['id'];
        $this->_helper->layout()->getView()->headTitle($this->view->pageTitle);

    }

    public function editAction()
    {
        $form = new C3op_Form_ProjectEdit;
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/projects/project/detail/?id=' . $postData['id']);
            } else {
                //form error: populate and go back
                $form->populate($postData);
                $field = $form->getElement('shortTitle');
                $shortTitle = $field->getValue();
                $this->view->form = $form;
            }
        } else {
            // GET
            $thisProject = $this->initProjectWithCheckedId($this->projectMapper);
            $id = $this->checkIdFromGet();
            $shortTitle = $thisProject->getShortTitle();
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $id);
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'title', $thisProject->getTitle());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'shortTitle', $thisProject->getShortTitle());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'client', $thisProject->getClient());
            $this->populateClientField($form, $thisProject->getClient());
            $this->populateResponsibleAtClientField($form, $thisProject->getClient(), $thisProject->getResponsibleAtClient());
            $this->populateOurResponsibleField($form, $thisProject->getOurResponsible());
            $this->setDateValueToFormField($form, 'beginDate', $thisProject->getBeginDate());
            $this->setDateValueToFormField($form, 'finishDate', $thisProject->getFinishDate());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'value', $thisProject->getValue());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'status', $thisProject->getStatus());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'contractNature', $thisProject->getContractNature());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'areaActivity', $thisProject->getAreaActivity());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'overhead', $thisProject->getOverhead());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'managementFee', $thisProject->getManagementFee());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'object', $thisProject->getObject());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'summary', $thisProject->getSummary());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'observation', $thisProject->getObservation());

        }
        $pageData = array('projectTitle' => $shortTitle);
        $this->view->pageData = $pageData;
        $this->view->pageTitle = $this->view->translate("#Edit project") . " " . $shortTitle;
        $this->view->pageUri = "/projects/project/edit/?id=$id";
        $this->_helper->layout()->getView()->headTitle($this->view->pageTitle);
    }

    public function engagementAction()
    {
        if (!isset($this->responsibleMapper)) {
            $this->initResponsibleMapper();
        }
        if (!isset($this->responsibleMapper)) {
            $this->initResponsibleMapper();
        }
        if (!isset($this->actionMapper)) {
            $this->initActionMapper();
        }
        if (!isset($this->contactMapper)) {
            $this->initContactMapper();
        }
        if (!isset($this->institutionMapper)) {
            $this->initInstitutionMapper();
        }
        if (!isset($this->outlayMapper)) {
            $this->initOutlayMapper();
        }

        $project = $this->initProjectWithCheckedId($this->projectMapper);
        $contactId = $this->checkContactFromGet();
        $contactName = $this->view->translate("#(undefined)");
        if ($contactId > 0) {
            $contact = $this->initContactWithCheckedContactId($this->contactMapper);
            $contactName = $contact->getName();
            $engagedType = C3op_Resources_ResponsibleTypeConstants::TYPE_TEAM_MEMBER;
        }
        $institutionId = $this->checkInstitutionFromGet();
        $institutionName = $this->view->translate("#(undefined)");
        if ($institutionId > 0) {
            $institution = $this->initInstitutionWithCheckedInstitutionId($this->institutionMapper);
            $institutionName = $institution->getShortName();
            $engagedType = C3op_Resources_ResponsibleTypeConstants::TYPE_OUTSIDE_SERVICE;
        }

        $actionsEngaged = $this->responsibleMapper->getAllActionsEngaging($project, $contactId, $institutionId);

        $engagedActions = array();
        $alreadyPayedValue = 0;
        $totalProvidedValue = 0;
        $totalContractedValue = 0;
        $currencyDisplay = new  C3op_Util_CurrencyDisplay();

        foreach ($actionsEngaged as $id => $data) {
            $action = $this->actionMapper->findById($id);
            $responsible = $this->responsibleMapper->findById($data['responsible']);

            $contractingStatus = new C3op_Projects_ActionContracting($action, $this->actionMapper);
            if ($contractingStatus->isContracted()) {
                $totalContractedValue += $responsible->getContractedValue();
                $actionTotalValue = $currencyDisplay->FormatCurrency($responsible->getContractedValue());
            } else if ($responsible->getPredictedValue() > 0) {
                $totalProvidedValue += $responsible->getPredictedValue();
                $actionTotalValue = $currencyDisplay->FormatCurrency($responsible->getPredictedValue());
            } else {
                $actionTotalValue = $this->view->translate("#(not defined)");
            }

            $actionPayedValue = $this->outlayMapper->totalPayedValueForResponsible($responsible);
            if ($actionPayedValue > 0) {
                $alreadyPayedValue += $actionPayedValue;
                $actionPayedValue = $currencyDisplay->FormatCurrency($actionPayedValue);
            } else {
                $actionPayedValue = $currencyDisplay->FormatCurrency(0);
            }

            $actionPayedValue = $this->outlayMapper->totalPayedValueForResponsible($responsible);
            if ($actionPayedValue > 0) {
                $alreadyPayedValue += $actionPayedValue;
                $actionPayedValue = $currencyDisplay->FormatCurrency($actionPayedValue);
            } else {
                $actionPayedValue = $currencyDisplay->FormatCurrency(0);
            }

            $statusTypes = new C3op_Projects_ActionStatusTypes();
            $rawActionStatus = $action->getStatus();
            $actionStatusLabel = $statusTypes->TitleForType($rawActionStatus);

            $user = Zend_Registry::get('user');
            $acl = Zend_Registry::get('acl');

            $tester = new C3op_Access_PrivilegeTester($user, $acl, "resources", "responsible", "contract");
            $rawResponsibleStatus = $responsible->getStatus();
            $statusTypes = new C3op_Resources_ResponsibleStatusTypes();
            $responsibleStatusLabel = $statusTypes->TitleForType($rawResponsibleStatus);
            if ($tester->allow()) {
                if (((($responsible->getType() == C3op_Resources_ResponsibleTypeConstants::TYPE_OUTSIDE_SERVICE)
                        && ($responsible->getInstitution() > 0))
                        || (($responsible->getType() == C3op_Resources_ResponsibleTypeConstants::TYPE_TEAM_MEMBER)
                        && ($responsible->getContact() > 0)))
                    && ($rawResponsibleStatus == C3op_Resources_ResponsibleStatusConstants::STATUS_FORESEEN)) {
                    $canContract = true;
                } else {
                    $canContract = false;
                }
            } else {
                $canContract = false;
            }

            $outlayId = 0;
            $canNotifyOutlay = false;
            $canProvideOutlay = false;
            if ($rawResponsibleStatus == C3op_Resources_ResponsibleStatusConstants::STATUS_CONTRACTED) {
                $doesIt = new C3op_Resources_ResponsibleHasCredit($responsible, $this->responsibleMapper);
                if ($doesIt->hasCreditToProvide()) {
                    $canProvideOutlay = true;
                } else {
                    $canProvideOutlay = false;
                }
                if ($doesIt->hasCreditToPay()) {
                    $result = $this->responsibleMapper->getNextOutlayToPayTo($responsible);
                    if ($result !== null) {
                        $canNotifyOutlay = true;
                        $outlayId = $result['id'];
                    } else {
                        $canNotifyOutlay = false;

                    }
                } else {
                    $canNotifyOutlay = false;
                }
            } else {
                $canProvideOutlay = false;
            }

            $removal = new C3op_Resources_ResponsibleRemoval($responsible, $this->responsibleMapper);
            if ($removal->canBeRemoved()) {
                $canEditResource = true;
                $canRemoveResponsible = true;
            } else {
                $canEditResource = false;
                $canRemoveResponsible = false;
            }


            $engagedActions[$id] = array(
                'responsibleId'        => $responsible->getId(),
                'title'               => $action->getTitle(),
                'position'            => "??? out of use ???",
//                'payedValue'          => $actionPayedValue,
//                'totalValue'          => $actionTotalValue,
                'payedValue'          => 'N/D',
                'totalValue'          => 'N/D',
                'actionStatus'        => $this->view->translate($actionStatusLabel),
                'responsibleStatus'    => $this->view->translate($responsibleStatusLabel),
                'canContractFlag'     => $canContract,
                'canProvideOutlay'    => $canProvideOutlay,
                'canNotifyOutlay'     => $canNotifyOutlay,
                'canEditResource'     => $canEditResource,
                'canRemoveResponsible' => $canRemoveResponsible,
                'outlayId'            => $outlayId,
            );
        }

        if ($totalProvidedValue > 0) {
            $totalProvidedValue = $currencyDisplay->FormatCurrency($totalProvidedValue);
        } else {
            $totalProvidedValue = $this->view->translate("#(not defined)");
        }

        if ($alreadyPayedValue > 0) {
            $alreadyPayedValue = $currencyDisplay->FormatCurrency($alreadyPayedValue);
        } else {
            $alreadyPayedValue = $currencyDisplay->FormatCurrency(0);
        }

        if ($totalContractedValue > 0) {
            $totalContractedValue = $currencyDisplay->FormatCurrency($totalContractedValue);
        } else {
            $totalContractedValue = $this->view->translate("#(not defined)");
        }



        $pageData = array(
            'projectId'       => $project->getId(),
            'type'            => $engagedType,
            'contactName'     => $contactName,
            'contactId'       => $contactId,
            'institutionName' => $institutionName,
            'institutionId'   => $institutionId,
            'projectTitle'    => $project->getShortTitle(),
//            'payedValue'      => $personPayedValue,
//            'contractedValue' => $personContractedValue,
//            'totalValue' => $personTotalValue,
            'payedValue'      => 'N/D',
            'contractedValue' => 'N/D',
            'totalValue'      => 'N/D',
            'engagedActions'   => $engagedActions,
        );

        $this->view->pageData = $pageData;

        if ($engagedType == C3op_Resources_ResponsibleTypeConstants::TYPE_TEAM_MEMBER) {
            $this->view->pageTitle = sprintf($this->view->translate("#%s's participation in the project"), $contactName);
            $this->view->pageUri = "/projects/index/engagement/?contact=$contactId";
        } else {
            $this->view->pageTitle = sprintf($this->view->translate("#%s's participation in the project"), $institutionName);
            $this->view->pageUri = "/projects/index/engagement/?institution=$institutionId";
        }
        $this->_helper->layout()->getView()->headTitle($this->view->pageTitle);

    }

    public function unacknowledgedAction()
    {

        $id = $this->checkIdFromGet();
        $thisProject = $this->projectMapper->findById($id);

        $this->initActionMapper();
        $list = $this->projectMapper->getAllUnacknowledgededActions($thisProject, $this->actionMapper);

        $unacknowledgededList = array();
        reset ($list);
        foreach ($list as $actionId) {
            $thisAction = $this->actionMapper->findById($actionId);
            $actionTitle = $thisAction->getTitle();
            $actionStart = C3op_Util_DateDisplay::FormatDateToShow(
                               $thisAction->getRealBeginDate()
                           );

            $unacknowledgededList[$actionId] = array(
                'actionId'       => $actionId,
                'actionTitle'    => $actionTitle,
                'actionStart'    => $actionStart,
            );
        }

        $this->view->unacknowledgededList = $unacknowledgededList;

    }

    private function initProjectMapper()
    {
         $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
    }

    private function initActionMapper()
    {
         $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
    }

    private function initContractMapper()
    {
         $this->contractMapper = new C3op_Projects_ContractMapper($this->db);
    }

    private function initOutlayMapper()
    {
        if (!isset($this->outlayMapper)) {
            $this->outlayMapper = new C3op_Finances_OutlayMapper($this->db);
        }
    }

    private function initMaterialSupplyMapper()
    {
         $this->materialSupplyMapper = new C3op_Resources_MaterialSupplyMapper($this->db);
    }

    private function initReceivableMapper()
    {
        if (!isset($this->receivableMapper)) {
            $this->receivableMapper = new C3op_Finances_ReceivableMapper($this->db);
        }
    }

    private function initResponsibleMapper()
    {
         $this->responsibleMapper = new C3op_Resources_ResponsibleMapper($this->db);
    }

    private function initInstitutionMapper()
    {
         $this->institutionMapper = new C3op_Register_InstitutionMapper($this->db);
    }

    private function initContactMapper()
    {
         $this->contactMapper = new C3op_Register_ContactMapper($this->db);
    }

    private function initLinkageMapper()
    {
         $this->linkageMapper = new C3op_Register_LinkageMapper($this->db);
    }

    private function initProjectWithCheckedId(C3op_Projects_ProjectMapper $mapper)
    {
        return $mapper->findById($this->checkIdFromGet());
    }

    private function initContactWithCheckedContactId(C3op_Register_ContactMapper $mapper)
    {
        return $mapper->findById($this->checkContactFromGet());
    }

    private function initInstitutionWithCheckedInstitutionId(C3op_Register_InstitutionMapper $mapper)
    {
        return $mapper->findById($this->checkInstitutionFromGet());
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
        throw new C3op_Projects_ProjectException("Invalid Project Id from Get");

    }

    private function checkContactFromGet()
    {
        $data = $this->_request->getParams();
        $filters = array(
            'contact' => new Zend_Filter_Alnum(),
        );
        $validators = array(
            'contact' => array('Digits', new Zend_Validate_GreaterThan(0)),
        );
        $input = new Zend_Filter_Input($filters, $validators, $data);
        if ($input->isValid()) {
            $contact = $input->contact;
            return $contact;
        }
        throw new C3op_Projects_ProjectException("Invalid Project Id from Get");

    }

    private function checkInstitutionFromGet()
    {
        $data = $this->_request->getParams();
        $filters = array(
            'institution' => new Zend_Filter_Alnum(),
        );
        $validators = array(
            'institution' => array('Digits', new Zend_Validate_GreaterThan(0)),
        );
        $input = new Zend_Filter_Input($filters, $validators, $data);
        if ($input->isValid()) {
            $institution = $input->institution;
            return $institution;
        }
        throw new C3op_Projects_ProjectException("Invalid Project Id from Get");

    }

    private function setDateValueToFormField(C3op_Form_ProjectCreate $form, $fieldName, $value)
    {
        $field = $form->getElement($fieldName);
        $validator = new C3op_Util_ValidDate;
        if ((!is_null($value)) && ($validator->isValid($value))) {
            $field->setValue($this->formatDataToShow($value));
        } else {
            $field->setValue("");
        }
    }

    private function formatDataToShow($rawData)
    {
        $dateArray = explode("-", $rawData);
        $formatedDate = $dateArray[2] . '/' . $dateArray[1] . '/' . $dateArray[0];
        return $formatedDate;
    }

    private function populateClientField(Zend_Form $form, $currentClient=0)
    {

        if (!isset($this->institutionMapper)) {
            $this->institutionMapper = new C3op_Register_InstitutionMapper($this->db);
        }
        $clientField = $form->getElement('client');
        $allPossibleClients = $this->institutionMapper->getAllPossibleClients();
        while (list($key, $institutionId) = each($allPossibleClients)) {
            $eachPossibleClient = $this->institutionMapper->findById($institutionId);
            $clientField->addMultiOption($institutionId, $eachPossibleClient->getName());
        }
        $clientField->setValue($currentClient);

    }

    private function populateOurResponsibleField(Zend_Form $form, $currentResponsible = 0)
    {
            if (!isset($this->contactMapper)) {
                $this->contactMapper = new C3op_Register_ContactMapper($this->db);
            }
            $ourResponsibleField = $form->getElement('ourResponsible');
            $allThatCanBeOurResponsible = $this->contactMapper->getAllContactThatAreLinkedToAContractant("name");
            while (list($key, $contactId) = each($allThatCanBeOurResponsible)) {
                $eachPossibleResponsible = $this->contactMapper->findById($contactId);
                $ourResponsibleField->addMultiOption($contactId, $eachPossibleResponsible->getName());
            }
            $ourResponsibleField->setValue($currentResponsible);
   }

    private function populateResponsibleAtClientField(Zend_Form $form, $institutionId=0, $currentResponsible = 0)
    {

            $element = $form->getElement('responsibleAtClient');
            if ($institutionId > 0) {
                if (!isset($this->institutionMapper)) {
                    $this->initInstitutionMapper();
                }
                if (!isset($this->contactMapper)) {
                    $this->initContactMapper();
                }

                $allThatCanBeResponsibleAtClient = $this->institutionMapper->getAllContactsThatAreLinkedToAnInstitution($institutionId);
                while (list($key, $contactData) = each($allThatCanBeResponsibleAtClient)) {
                    $contactId = $contactData['contactId'];
                    $eachPossibleResponsible = $this->contactMapper->findById($contactId);
                    $element->addMultiOption($contactId, $eachPossibleResponsible->getName());
                }
                $element->setValue($currentResponsible);
            } else {
                $element->setValue(0);

            }
   }

   private function fillDataTree($tree)
    {
       // actionInfo
       //   title
       //   subordinatedTo
       //   responsibleName
       //   predictedBeginDate
       //   realBeginDate
       //   predictedFinishDate
       //   realFinishDate
       //

       $this->initActionMapper();
        foreach ($tree as $id => $subTree) {
            $loopAction = $this->actionMapper->findById($id);
            $data = array();
            $data['title'] = $loopAction->getTitle();
            $data['subordinatedTo'] = $loopAction->getSubordinatedTo();

            $actionStatus = $loopAction->getStatus();
            $statusTypes = new C3op_Projects_ActionStatusTypes();
            $actionStatusLabel = $statusTypes->TitleForType($actionStatus);
            $data['statusLabel'] = $actionStatusLabel;

            if ($loopAction->getSupervisor()) {
                $theContact = $this->contactMapper->findById($loopAction->getSupervisor());
                $data['responsibleName'] = $theContact->getName();
            } else {
                $data['responsibleName'] = $this->view->translate("#Not defined");
            }



            $responsible = new C3op_Projects_ActionResponsible($loopAction, $this->actionMapper, $this->db);
            if ($responsible->doesItHaveAResponsible()) {
                $responsibleData = $responsible->fetch();
                $data['hasResponsible'] = true;
                $data['contactId'] = $responsibleData['contactId'];
                $data['institutionId'] = $responsibleData['institutionId'];
                $data['responsibleLabel'] = $responsibleData['responsibleLabel'];
                $data['responsibleStatusLabel'] = $responsibleData['statusLabel'];
                $data['responsibleType'] = $responsibleData['responsibleType'];
            } else {
                $data['hasResponsible'] = false;
                $data['contactId'] = 0;
                $data['contactName'] = _('#(unassigned)');
                $data['responsibleStatusLabel'] = _('#(unknown)');
                $data['responsibleLabel'] = _('#(unassigned)');

            }




            $data['predictedBeginDate'] = C3op_Util_DateDisplay::FormatDateToShow($loopAction->getPredictedBeginDate());
            $data['realBeginDate'] = C3op_Util_DateDisplay::FormatDateToShow($loopAction->getRealBeginDate());
            $data['predictedFinishDate'] = C3op_Util_DateDisplay::FormatDateToShow($loopAction->getPredictedFinishDate());
            $data['realFinishDate'] = C3op_Util_DateDisplay::FormatDateToShow($loopAction->getRealFinishDate());

            $this->treeData[$id] = $data;

            $this->fillDataTree($subTree);
        }
    }

    public  function populateResponsibleAtClientAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $id = $this->checkIdFromGet();
        $data = $this->fillContactsDataFrom($id);

        echo json_encode($data);


    }


 public function payablesAction()
    {
        $id = $this->checkIdFromGet();
        $thisProject = $this->projectMapper->findById($id);

        $this->initActionMapper();
        $list = $this->projectMapper->getAllDoneActions($thisProject);

        $payablesList = array();
        reset ($list);
        foreach ($list as $actionId) {
            $thisAction = $this->actionMapper->findById($actionId);
            $actionTitle = $thisAction->GetTitle();

            $currencyDisplay = new  C3op_Util_CurrencyDisplay();
            $contractValue = $currencyDisplay->FormatCurrency(
                               $this->actionMapper->getContractedValueJustForThisAction($thisAction)
                           );

            $payablesList[$actionId] = array(
                'actionId'       => $actionId,
                'actionTitle'    => $actionTitle,
                'actionValue'    => $contractValue,
            );
        }

        $this->view->payablesList = $payablesList;

    }

     private function getMaterialSuppliesList(C3op_Projects_Project $project)
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

        if (!isset($this->materialSupplyMapper)) {
            $this->initmaterialSupplyMapper();
        }

        if (!isset($this->linkageMapper)) {
            $this->initLinkageMapper();
        }

        $materialSuppliesList = array();
        $materialSuppliesIdsList = $this->materialSupplyMapper->getAllMaterialSuppliesContractedOrPredictedAt($project);

        foreach ($materialSuppliesIdsList as $materialSupplyId) {
            $theMaterialSupply = $this->materialSupplyMapper->findById($materialSupplyId);
            $currencyDisplay = new  C3op_Util_CurrencyDisplay();
            $currencyValue = $currencyDisplay->FormatCurrency($theMaterialSupply->GetTotalValue());
            //$totalValueExistentOutlays = $this->calculateTotalValueExistentOutlays($theMaterialSupply);
            $totalValueExistentOutlays = "???";

            $descriptionMessage = $theMaterialSupply->GetDescription();

            $institutionId = $theMaterialSupply->GetInstitution();
            //$actionId = $action->GetId();
            $institutionName = $this->view->translate("(#not defined)");
            if ($institutionId > 0) {
                $this->initContactMapper();
                $this->initInstitutionMapper();
                $institutionService = $this->institutionMapper->findById($institutionId);
                $institutionName = $institutionService->GetName();
            }


            $status = $theMaterialSupply->getStatus();
            $statusTypes = new C3op_Resources_MaterialSupplyStatusTypes();
            $statusLabel = $statusTypes->TitleForType($status);

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
                'id'                     => $materialSupplyId,
                'name'                   => $institutionName,
                'description'            => $descriptionMessage,
                'value'                  => "N/D",
//                'value'                  => $currencyValue,
                'contractingStatusLabel' => $statusLabel,
                'canContractFlag'        => $canContract,
                'canRemoveMaterialSupply'    => $canRemoveMaterialSupply,
                'canProvideOutlay'       => $canProvideOutlay,

            );
        }

        return $materialSuppliesList;

    }

    private function fillContactsDataFrom($institutionId)
    {
            if (!isset($this->institutionMapper)) {
                $this->initInstitutionMapper();
            }
            if (!isset($this->contactMapper)) {
                $this->initContactMapper();
            }

            $contactsList = $this->institutionMapper->getAllContactsThatAreLinkedToAnInstitution($institutionId);
            $data = array();
            foreach ($contactsList as $contactData) {
                $contactId = $contactData['contactId'];
                $loopContact = $this->contactMapper->findById($contactId);
                $data[] = array('id' => $contactId, 'title' => $loopContact->getName());
            }

            return $data;

    }

    private function fillProjectHeaderData(C3op_Projects_Project $projectToBeDetailed)
    {

        if (!isset($this->projectMapper)) {
            $this->initProjectMapper();
        }
        if (!isset($this->institutionMapper)) {
            $this->initInstitutionMapper();
        }
        if (!isset($this->contactMapper)) {
            $this->initContactMapper();
        }
        if (!isset($this->contractMapper)) {
            $this->initContractMapper();
        }

        //  projectHeader
        //    id
        //    projectTitle
        //    clientName
        //    areaActivity
        //    ourResponsible
        //    responsibleAtClient
        //    overhead
        //    managementFee
        //    projectValue
        //    projectDates
        //    hasContract
        //    amendmentsList

        $projectTitle = $projectToBeDetailed->getShortTitle();

        $clientName = $this->view->translate('#(not defined)');
        if ($projectToBeDetailed->getClient() > 0) {
            $thisClient = $this->institutionMapper->findById($projectToBeDetailed->getClient());
            $clientName = $thisClient->GetShortName();
        }

        $obj = new C3op_Projects_AreaActivityTypes();
        $areaActivity = $obj->TitleForType($projectToBeDetailed->getAreaActivity());

        if ($projectToBeDetailed->getOurResponsible()) {
            $theContact = $this->contactMapper->findById($projectToBeDetailed->getOurResponsible());
            $ourResponsible = $theContact->getName();
        } else {
            $ourResponsible = $this->view->translate("#Not defined");
        }

        if ($projectToBeDetailed->getResponsibleAtClient()) {
            $theContact = $this->contactMapper->findById($projectToBeDetailed->getResponsibleAtClient());
            $responsibleAtClient = $theContact->getName();
        } else {
            $responsibleAtClient = $this->view->translate("#Not defined");
        }

        $overhead = $projectToBeDetailed->getOverhead();
        if ($overhead === null) {
            $overhead = $this->view->translate("#N/A");
        }
        $managementFee = $projectToBeDetailed->getManagementFee();
        if ($managementFee === null) {
            $managementFee = $this->view->translate("#N/A");
        }
        $currencyDisplay = new  C3op_Util_CurrencyDisplay();
        $projectValue = $currencyDisplay->FormatCurrency($projectToBeDetailed->getValue());

        $validator = new C3op_Util_ValidDate();
        if (($validator->isValid($projectToBeDetailed->getBeginDate())) && ($validator->isValid($projectToBeDetailed->getFinishDate()))) {
            $projectDates = sprintf($this->view->translate("#%s until %s"),
                    C3op_Util_DateDisplay::FormatDateToShow($projectToBeDetailed->getBeginDate()),
                    C3op_Util_DateDisplay::FormatDateToShow($projectToBeDetailed->getFinishDate())
                    );
        } else {
            $projectDates = $this->view->translate("#Undefined dates");
        }

        $contracts = $this->projectMapper->getAllContracts($projectToBeDetailed);

        $amendmentsList = array();
        if (count($contracts)) {
            $hasContract = true;
            $amendments = $this->projectMapper->getAllAmendments($projectToBeDetailed);
            foreach ($amendments as $id) {
                $loopContract = $this->contractMapper->findById($id);
                $contractData = array(
                    'signingDate' =>  $loopContract->getSigningDate(),
                );
                $amendmentsList[$id] = $contractData;

            }
        } else {
            $hasContract = false;
        }

       $projectHeader = array(
                'id'                  => $projectToBeDetailed->getId(),
                'projectTitle'        => $projectTitle,
                'clientName'          => $clientName,
                'areaActivity'        => $areaActivity,
                'ourResponsible'      => $ourResponsible,
                'responsibleAtClient' => $responsibleAtClient,
                'overhead'            => $overhead,
                'managementFee'       => $managementFee,
                'projectValue'        => $projectValue,
                'projectDates'        => $projectDates,
                'hasContract'         => $hasContract,
                'amendmentsList'      => $amendmentsList,
            );

        return $projectHeader;



    }

     private function getResponsibleData(C3op_Resources_Responsible $responsible)
    {
        //   responsibleData
        //      id
        //      contactId
        //      linkageId
        //      name
        //      description
        //      value
        //      contractingStatusLabel
        //      canContractFlag
        //      canRemoveResponsible
        //      canEditResource
        //      canProvideOutlay
        $this->initActionMapper();
        $this->initResponsibleMapper();

        $responsible = $this->responsibleMapper->findById($responsibleId);
        $responsibleAction = $this->actionMapper->findById($responsible->getAction());

        $fetcher = new C3op_Projects_ActionResponsible($responsibleAction, $this->actionMapper, $this->db);

        $responsibleData = $fetcher->fetch();

        $responsibleActionTitle = $responsibleAction->getTitle();

        $contactId = $responsibleData['contactId'];
        $contactName = $responsibleData['contactName'];
        $institutionId = $responsibleData['institutionId'];
        $statusLabel = $this->view->translate($responsibleData['statusLabel']);
        $canContract = $responsibleData['canContract'];
        $canDismiss = $responsibleData['canDismiss'];
        $canProvideOutlay = $responsibleData['canProvideOutlay'];

        $status = $responsible->getStatus();

        $removal = new C3op_Resources_ResponsibleRemoval($responsible, $this->responsibleMapper);
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
            'name'                   => $contactName,
            'responsibleActionId'    => $responsible->getAction(),
            'responsibleActionTitle' => $responsibleActionTitle,
            'contractingStatusLabel' => $statusLabel,
            'canContractFlag'        => $canContract,
            'canDismissFlag'         => $canDismiss,
            'canRemoveResponsible'   => $canRemoveResponsible,
            'canEditResource'        => $canEditResource,
            'canProvideOutlay'       => $canProvideOutlay,

        );

        return $responsiblesList;

    }

}