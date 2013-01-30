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
    private $teamMemberMapper;
    private $outsideServiceMapper;
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
                $this->_redirect('/projects/project/success/?id=' . $id);
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
                $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('#The record was successfully updated.');
                $this->_redirect('/projects/project/success');
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

        // receivablesList
        //   * id =>
        //      receivableTitle
        //      predictedDate
        //      realDate
        //      predictedValue
        //      realValue

        $receivablesList = array();

        if (!isset($this->receivableMapper)) {
            $this->initReceivableMapper();
        }

        $projectReceivables = $this->receivableMapper->getAllReceivables($projectToBeDetailed);

        foreach ($projectReceivables as $id) {
            $theReceivable = $this->receivableMapper->findById($id);
            $receivableTitle = $theReceivable->getTitle();

            $receivableDescription = $theReceivable->getDescription();

            $validator = new C3op_Util_ValidDate();
            if ($validator->isValid($theReceivable->getPredictedDate())) {
                $predictedDate = C3op_Util_DateDisplay::FormatDateToShow($theReceivable->getPredictedDate());
            } else {
                $predictedDate = $this->view->translate("#(undefined)");
            }

            if ($validator->isValid($theReceivable->getRealDate())) {
                $realDate = C3op_Util_DateDisplay::FormatDateToShow($theReceivable->getRealDate());
                $canNotifyReceiving = false;
            } else {
                $realDate = $this->view->translate("#(not received)");
                $canNotifyReceiving = true;
            }

            $currencyDisplay = new  C3op_Util_CurrencyDisplay();

            $predictedValue = $currencyDisplay->FormatCurrency($theReceivable->getPredictedValue());
            $realValue = $currencyDisplay->FormatCurrency($theReceivable->getRealValue());

            $receivablesList[$id] = array(
                    'receivableTitle'       => $receivableTitle,
                    'receivableDescription'   => $receivableDescription,
                    'predictedDate'      => $predictedDate,
                    'realDate'           => $realDate,
                    'predictedValue'     => $predictedValue,
                    'realValue'          => $realValue,
                    'canNotifyReceiving' => $canNotifyReceiving,
                );
        }

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

        // outlaysList
        //   * id =>
        //      actionId
        //      actionTitle
        //      payeeId
        //      payeeName
        //      predictedDate
        //      realDate
        //      predictedValue
        //      realValue

        $outlaysList = array();
        $projectOutlays = $this->projectMapper->getAllOutlaysOf($projectToBeDetailed);
        if (!isset($this->outlayMapper)) {
            $this->initOutlayMapper();
        }
        if (!isset($this->teamMemberMapper)) {
            $this->initTeamMemberMapper();
        }
        if (!isset($this->linkageMapper)) {
            $this->initLinkageMapper();
        }
        if (!isset($this->contactMapper)) {
            $this->initContactMapper();
        }

        foreach ($projectOutlays as $id) {
            $theOutlay = $this->outlayMapper->findById($id);
            $actionId = $theOutlay->getAction();
            $theAction = $this->actionMapper->findById($actionId);
            $actionTitle = $theAction->getTitle();
            $payeeName = $this->view->translate("#Not defined");
            $payeeId = 0;
            if ($theOutlay->getTeamMember() > 0) {
                $theTeamMember = $this->teamMemberMapper->findById($theOutlay->getTeamMember());
                if ($theTeamMember->getLinkage() > 0) {

                    $theLinkage = $this->linkageMapper->findById($theTeamMember->getLinkage());
                    $theContact = $this->contactMapper->findById($theLinkage->getContact());

                    $payeeId = $theContact->getId();
                    $payeeName = $theContact->getName();

                    $status = $theTeamMember->getStatus();
                    if ($status == C3op_Resources_TeamMemberStatusConstants::STATUS_CONTRACTED) {
                        $doesIt = new C3op_Resources_TeamMemberHasCredit($theTeamMember, $this->teamMemberMapper);
                        if ($doesIt->hasCreditToPay()) {
                            $canNotifyOutlay = true;
                        } else {
                            $canNotifyOutlay = false;
                        }
                    } else {
                        $canNotifyOutlay = false;
                    }




                }
            }

            $validator = new C3op_Util_ValidDate();

            if ($validator->isValid($theOutlay->getRealDate())) {
                $realDate = C3op_Util_DateDisplay::FormatDateToShow($theOutlay->getRealDate());
            } else {
                $realDate = $this->view->translate('#(undefined)');
            }

            if ($validator->isValid($theOutlay->getPredictedDate())) {
                $predictedDate = C3op_Util_DateDisplay::FormatDateToShow($theOutlay->getPredictedDate());
            } else {
                $predictedDate = $this->view->translate('#(undefined)');
            }
            $currencyDisplay = new  C3op_Util_CurrencyDisplay();
            $predictedValue = $currencyDisplay->FormatCurrency($theOutlay->getPredictedValue());
            $realValue = $currencyDisplay->FormatCurrency($theOutlay->getRealValue());


            $outlaysList[$id] = array(
                    'actionId' => $actionId,
                    'actionTitle' => $actionTitle,
                    'payeeId' => $payeeId,
                    'payeeName' => $payeeName,
                    'predictedDate' => $predictedDate,
                    'realDate' => $realDate,
                    'predictedValue' => $predictedValue,
                    'realValue' => $realValue,
                    'canNotifyOutlay' => $canNotifyOutlay,
                );
        }

        // teamMembersList
        //   * id =>
        //      staffName
        //      actionId
        //      linkageId
        //      contactId
        //      linkageInstitutionName
        //      positionDescription
        //      staffEmail
        //      staffPhoneNumber

        $teamMembersList = array();
        if (!isset($this->teamMemberMapper)) {
            $this->initTeamMemberMapper();
        }
        $projectTeam = $this->teamMemberMapper->getAllUniqueTeamMembersContractedAt($projectToBeDetailed);
        if (!isset($this->linkageMapper)) {
            $this->initLinkageMapper();
        }

        $rolesArray = $this->teamMemberMapper->getAllUnassignedPositionsAt($projectToBeDetailed);
        foreach ($rolesArray as $id) {
            $projectTeam[] = $id;
        }


        foreach ($projectTeam as $id) {
            $theTeamMember = $this->teamMemberMapper->findById($id);

            // init labels
            $staffName = $this->view->translate("#To be defined");
            $staffId = 0;
            $emailString = $this->view->translate("#---");
            $phoneNumberString = $this->view->translate("#---");


            if ($theTeamMember->getLinkage() > 0) {


                $valuestPosition = $this->teamMemberMapper->findMainPositionForAPerson($theTeamMember);

                if ($valuestPosition->getId() != $theTeamMember->getId()) {
                    $theTeamMember = $valuestPosition;
                }

                $theLinkage = $this->linkageMapper->findById($theTeamMember->getLinkage());
                $theContact = $this->contactMapper->findById($theLinkage->getContact());
                $theInstitution = $this->institutionMapper->findById($theLinkage->getInstitution());

                $staffId = $theContact->getId();
                $staffName = $theContact->getName();
                $linkageInstitutionName = $theInstitution->getShortName();
                $data = $theLinkage->getEmails();
                if (count($data)) {
                    $staffEmail = reset($data);
                    $emailString = $staffEmail->getAddress();
                }
                $data = $theLinkage->getPhoneNumbers();
                if (count($data)) {
                    $staffPhoneNumber = reset($data);
                    $phoneNumberString = "({$staffPhoneNumber->getAreaCode()}) {$staffPhoneNumber->getLocalNumber()}";
                }

            }



            $actionId = $theTeamMember->getAction();
            $theAction = $this->actionMapper->findById($actionId);
            $actionTitle = $theAction->getTitle();
            $removal = new C3op_Resources_TeamMemberRemoval($theTeamMember, $this->teamMemberMapper);

            if ($removal->canBeRemoved()) {
                $canRemoveTeamMember = true;
            } else {
                $canRemoveTeamMember = false;
            }

            $positionDescription = $theTeamMember->getDescription();


            $teamMembersList[$id] = array(
                    'contactId'              => $staffId,
                    'linkageId'              => $theLinkage->getId(),
                    'linkageInstitutionName' => $linkageInstitutionName,
                    'actionId'               => $theTeamMember->getAction(),
                    'positionDescription'    => $positionDescription,
                    'staffName'              => $staffName,
                    'staffPhoneNumber'       => $phoneNumberString,
                    'staffEmail'             => $emailString,
                    'canRemoveTeamMember'    => $canRemoveTeamMember,
                );
        }

        // outsideServicesList
        //   * id =>
        //      institutionName
        //      actionId
        //      institutionId
        //      serviceDescription
        //      institutionEmail
        //      institutionPhoneNumber

        if (!isset($this->outsideServiceMapper)) {
            $this->initOutsideServiceMapper();
        }
        $outsideServicesList = array();
        $projectTeam = $this->outsideServiceMapper->getAllOutsideServicesContractedOrPredictedAt($projectToBeDetailed);
        if (!isset($this->linkageMapper)) {
            $this->initLinkageMapper();
        }

        foreach ($projectTeam as $id) {
            $theOutsideService = $this->outsideServiceMapper->findById($id);
            $actionId = $theOutsideService->getAction();
            $theAction = $this->actionMapper->findById($actionId);
            $actionTitle = $theAction->getTitle();
//            $institutionName = $this->view->translate("#Not defined");
//            $institutionId = 0;
//            $institutionEmail = $this->view->translate("#Not defined");
//            $institutionPhoneNumber = $this->view->translate("#Not defined");
//            if ($theOutsideService->getLinkage() > 0) {
//                $theLinkage = $this->linkageMapper->findById($theOutsideService->getLinkage());
//                $theContact = $this->contactMapper->findById($theLinkage->getContact());
//                $institutionId = $theContact->getId();
//                $institutionName = $theContact->getName();
//                $institutionEmail = $this->view->translate("#Not implemented");
//                $institutionPhoneNumber = $this->view->translate("#Not implemented");
//            }

            $institutionEmail = $this->view->translate("#Not implemented");
            $institutionPhoneNumber = $this->view->translate("#Not implemented");
            $institutionName = $this->view->translate("#Not defined");
            $institutionId = $theOutsideService->GetInstitution();
            $institutionName = $this->view->translate("(#not defined)");
            if ($institutionId > 0) {
                $this->initContactMapper();
                $this->initInstitutionMapper();
                $institutionService = $this->institutionMapper->findById($institutionId);
                $institutionName = $institutionService->GetName();
            }



            $removal = new C3op_Resources_OutsideServiceRemoval($theOutsideService, $this->outsideServiceMapper);

            if ($removal->canBeRemoved()) {
                $canRemoveOutsideService = true;
            } else {
                $canRemoveOutsideService = false;
            }


            $serviceDescription = $theOutsideService->getDescription();

            $outsideServicesList[$id] = array(
                    'institutionId'           => $institutionId,
                    'actionId'                => $theOutsideService->getAction(),
                    'serviceDescription'      => $serviceDescription,
                    'institutionName'         => $institutionName,
                    'institutionPhoneNumber'  => $institutionPhoneNumber,
                    'institutionEmail'        => $institutionEmail,
                    'canRemoveOutsideService' => $canRemoveOutsideService,
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
            'receivablesList'      => $receivablesList,
            'productsList'         => $productsList,
            'outlaysList'          => $outlaysList,
            'actionsTree'          => $actionTreeList,
            'teamMembersList'      => $teamMembersList,
            'outsideServicesList'  => $outsideServicesList,
            'materialSuppliesList' => $materialSuppliesList,
            'detailsData'          => $detailsData,

        );
        $this->view->pageData = $pageData;
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
                $this->_redirect('/projects/project/success');
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
    }

    public function personAction()
    {
        if (!isset($this->linkageMapper)) {
            $this->initLinkageMapper();
        }
        if (!isset($this->teamMemberMapper)) {
            $this->initTeamMemberMapper();
        }
        if (!isset($this->actionMapper)) {
            $this->initActionMapper();
        }
        if (!isset($this->contactMapper)) {
            $this->initContactMapper();
        }
        if (!isset($this->outlayMapper)) {
            $this->initOutlayMapper();
        }

        $project = $this->initProjectWithCheckedId($this->projectMapper);
        $linkage = $this->initLinkageWithCheckedLinkageId($this->linkageMapper);
        $contact = $this->contactMapper->findById($linkage->getContact());

        $actionsEngaged = $this->teamMemberMapper->getAllActionsEngaging($linkage, $project);

        $personActions = array();
        $personPayedValue = 0;
        $personTotalValue = 0;
        $currencyDisplay = new  C3op_Util_CurrencyDisplay();

        foreach ($actionsEngaged as $id => $data) {
            $action = $this->actionMapper->findById($id);
            $teamMember = $this->teamMemberMapper->findById($data['teamMember']);

            if ($teamMember->getValue() > 0) {
                $personTotalValue += $teamMember->getValue();
                $actionTotalValue = $currencyDisplay->FormatCurrency($teamMember->getValue());
            } else {
                $actionTotalValue = $this->view->translate("#(not defined)");
            }

            $actionPayedValue = $this->outlayMapper->totalPayedValueForTeamMember($teamMember);
            if ($actionPayedValue > 0) {
                $personPayedValue += $actionPayedValue;
                $actionPayedValue = $currencyDisplay->FormatCurrency($actionPayedValue);
            } else {
                $actionPayedValue = $currencyDisplay->FormatCurrency(0);
            }

            $statusTypes = new C3op_Projects_ActionStatusTypes();
            $rawActionStatus = $action->getStatus();
            $actionStatusLabel = $statusTypes->TitleForType($rawActionStatus);

            $rawTeamMemberStatus = $teamMember->getStatus();
            $outlayId = 0;
            if ($rawTeamMemberStatus == C3op_Resources_TeamMemberStatusConstants::STATUS_CONTRACTED) {
                $doesIt = new C3op_Resources_TeamMemberHasCredit($teamMember, $this->teamMemberMapper);
                if ($doesIt->hasCreditToProvide()) {
                    $canProvideOutlay = true;
                } else {
                    $canProvideOutlay = false;
                }
                if ($doesIt->hasCreditToPay()) {
                    $canNotifyOutlay = true;
                    $result = $this->teamMemberMapper->getNextOutlayToPayTo($teamMember);
                    if ($result !== null) {
                        $outlayId = $result['id'];
                    }
                } else {
                    $canNotifyOutlay = false;
                }
            } else {
                $canProvideOutlay = false;
            }

            $personActions[$id] = array(
                'teamMemberId'     => $teamMember->getId(),
                'title'            => $action->getTitle(),
                'position'         => $teamMember->getDescription(),
                'payedValue'       => $actionPayedValue,
                'totalValue'       => $actionTotalValue,
                'status'           => $this->view->translate($actionStatusLabel),
                'canProvideOutlay' => $canProvideOutlay,
                'canNotifyOutlay'  => $canNotifyOutlay,
                'outlayId'         => $outlayId,
            );
        }

        if ($personTotalValue > 0) {
            $personTotalValue = $currencyDisplay->FormatCurrency($personTotalValue);
        } else {
            $personTotalValue = $this->view->translate("#(not defined)");
        }

        if ($personPayedValue > 0) {
            $personPayedValue = $currencyDisplay->FormatCurrency($personPayedValue);
        } else {
            $personPayedValue = $currencyDisplay->FormatCurrency(0);
        }



        $personData = array(
            'projectId' => $project->getId(),
            'name' => $contact->getName(),
            'projectTitle' => $project->getShortTitle(),
            'payedValue' => $personPayedValue,
            'totalValue' => $personTotalValue,
            'personActions' => $personActions,
        );

        $pageData = array(
            'personData' => $personData,
        );
        $this->view->pageData = $pageData;

    }

    public function receivablesAction()
    {
        $receivableMapper = new C3op_Finances_ReceivableMapper($this->db);

        $id = $this->checkIdFromGet();
        $thisProject = $this->projectMapper->findById($id);
        $receivablesIdList = $this->receivableMapper->getAllReceivables($thisProject);
        $receivablesList = array();
        reset ($receivablesList);
        $receivablesTotalValue = 0;
        $receivablesCounter = 0;
        foreach ($receivablesIdList as $receivableId) {
            $thisReceivable = $receivableMapper->findById($receivableId);
            $receivablesCounter++;
            if ($thisReceivable->getTitle()) {
                $title = $thisReceivable->getTitle();
            } else {
                $title = "($receivablesCounter)";
            }

            $validator = new C3op_Util_ValidDate();
            if ($validator->isValid($thisReceivable->getPredictedDate())) {
                $predictedDate = C3op_Util_DateDisplay::FormatDateToShow($thisReceivable->getPredictedDate());
            } else {
                $predictedDate = $this->view->translate("#(unknown date)");
            }

            if ($thisReceivable->getPredictedValue() > 0) {
                $receivablesTotalValue += $thisReceivable->getPredictedValue();
                $currencyDisplay = new  C3op_Util_CurrencyDisplay();
                $predictedValue = $currencyDisplay->FormatCurrency($thisReceivable->getPredictedValue());
            } else {
                $predictedValue = "";
            }

            $productsIdList = $receivableMapper->getAllProducts($thisReceivable);
            $productsList = array();
            foreach ($productsIdList as $productId) {
                $actionMapper = new C3op_Projects_ActionMapper($this->db);
                $thisAction = $actionMapper->findById($productId);
                $actionTitle =  sprintf("<a href=/projects/action/detail/?id=%d>%s</a>", $productId, $thisAction->getTitle());
                $productsList[$productId] = array(
                    'title' => $actionTitle,
                    'linkDetail' => '/projects/action/detail/?id=' . $productId   ,
                );

            }

            $receivablesList[$receivableId] = array(
                'title' => $title,
                'productsList' => $productsList,
                'predictedDate' => $predictedDate,
                'predictedValue' => $predictedValue,
                'editLink' => '/finances/receivable/edit/?id=' . $receivableId   ,
            );
        }

        $currencyDisplay = new  C3op_Util_CurrencyDisplay();
        if ($receivablesTotalValue == $thisProject->getValue()) {
            $projectValue = $currencyDisplay->FormatCurrency($receivablesTotalValue) . " (OK)";
        } else {
            $projectValue = "Valor do Projeto: " . $currencyDisplay->FormatCurrency($thisProject->getValue());
            $projectValue .= " Total dos recebimentos:" .  $currencyDisplay->FormatCurrency($receivablesTotalValue) . " (?)";

        }

        $projectInfo = array(
            'title' => $thisProject->getShortTitle(),
            'linkDetail' => '/projects/project/detail/?id=' . $id   ,
            'projectValue' => $projectValue,
            'editLink' => '/projects/project/edit/?id=' . $id   ,
            'receivablesList' => $receivablesList,
        );

        $this->view->projectInfo = $projectInfo;
    }

    public function successAction()
    {
        if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();
            $this->getResponse()->setHeader('Refresh', '1; URL=/projects');
        } else {
            $this->_redirect('/projects');
        }
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

    private function initOutsideServiceMapper()
    {
         $this->outsideServiceMapper = new C3op_Resources_OutsideServiceMapper($this->db);
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

    private function initTeamMemberMapper()
    {
         $this->teamMemberMapper = new C3op_Resources_TeamMemberMapper($this->db);
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

    private function initLinkageWithCheckedLinkageId(C3op_Register_LinkageMapper $mapper)
    {
        return $mapper->findById($this->checkLinkageFromGet());
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

    private function checkLinkageFromGet()
    {
        $data = $this->_request->getParams();
        $filters = array(
            'linkage' => new Zend_Filter_Alnum(),
        );
        $validators = array(
            'linkage' => array('Digits', new Zend_Validate_GreaterThan(0)),
        );
        $input = new Zend_Filter_Input($filters, $validators, $data);
        if ($input->isValid()) {
            $linkage = $input->linkage;
            return $linkage;
        }
        throw new C3op_Projects_ProjectException("Invalid Project Id from Get");

    }

    private function setDateValueToFormField(C3op_Form_ProjectCreate $form, $fieldName, $value)
    {
        $field = $form->getElement($fieldName);
        $validator = new C3op_Util_ValidDate;
        if (($value != '0000-00-00') && ($validator->isValid($value))) {
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
            $allThatCanBeOurResponsible = $this->contactMapper->getAllContactThatAreLinkedToAContractant();
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
                while (list($key, $contactId) = each($allThatCanBeResponsibleAtClient)) {
                    $eachPossibleResponsible = $this->contactMapper->findById($contactId);
                    $element->addMultiOption($contactId, $eachPossibleResponsible->getName());
                }
                $element->setValue($currentResponsible);
            } else {
                $element->setValue(0);

            }
   }

    private function outlayAsAParcel(C3op_Finances_Outlay $outlay)
    {
        $teamMemberId = $outlay->getTeamMember();
        if (!isset($this->teamMemberMapper)) {
            $this->teamMemberMapper = new C3op_Resources_TeamMemberMapper($this->db);
        }
        $outlayTeamMember = $this->teamMemberMapper->findById($teamMemberId);
        $listOutlaysForTeamMember = $this->teamMemberMapper->getAllOutlays($outlayTeamMember);
        $totalParcels = count($listOutlaysForTeamMember);

        $parcelsCount = 0;
        foreach($listOutlaysForTeamMember as $parcelId) {
            $thisParcel = $this->outlayMapper->FindById($parcelId);
            $parcelsCount++;
            if ($thisParcel->getId() == $outlay->getId()) {
                $myParcel = $parcelsCount;
            }
        }
        return "$myParcel/$totalParcels";
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

            if ($loopAction->getResponsible()) {
                $theContact = $this->contactMapper->findById($loopAction->getResponsible());
                $data['responsibleName'] = $theContact->getName();
            } else {
                $data['responsibleName'] = $this->view->translate("#Not defined");
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
        if (!isset($this->institutionMapper)) {
            $this->initInstitutionMapper();
        }
        if (!isset($this->contactMapper)) {
            $this->initContactMapper();
        }

        $contactsList = $this->institutionMapper->getAllContactsThatAreLinkedToAnInstitution($id);
        $data = array();
        foreach ($contactsList as $k => $id) {
            $loopContact = $this->contactMapper->findById($id);
            $data[] = array('id' => $id, 'title' => $loopContact->getName());
        }

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
                'value'                  => $currencyValue,
                'contractingStatusLabel' => $statusLabel,
                'canContractFlag'        => $canContract,
                'canRemoveMaterialSupply'    => $canRemoveMaterialSupply,
                'canProvideOutlay'       => $canProvideOutlay,

            );
        }

        return $materialSuppliesList;

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


}