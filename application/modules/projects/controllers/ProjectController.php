<?php

class Projects_ProjectController extends Zend_Controller_Action
{
    private $projectMapper;
    private $actionMapper;
    private $db;
    private $institutionMapper;
    private $contactMapper;
    private $outlayMapper;
    private $receivableMapper;
    private $teamMemberMapper;
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

    public function editAction()
    {
        $form = new C3op_Form_ProjectEdit;
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
                $field = $form->getElement('title');
                $title = $field->getValue();
                $this->view->form = $form;
            }
        } else {
            // GET
            $thisProject = $this->InitProjectWithCheckedId($this->projectMapper);
            $id = $this->checkIdFromGet();
            $title = $thisProject->GetTitle();
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $id);
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'title', $thisProject->getTitle());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'shortTitle', $thisProject->getShortTitle());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'client', $thisProject->getClient());
            $this->populateClientField($form, $thisProject->getClient());
            $this->populateResponsibleAtClientField($form, $thisProject->getClient(), $thisProject->getResponsibleAtClient());
            $this->populateOurResponsibleField($form, $thisProject->getOurResponsible());
            //C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'responsibleAtClient', $thisProject->getResponsibleAtClient());
            $this->SetDateValueToFormField($form, 'beginDate', $thisProject->getBeginDate());
            $this->SetDateValueToFormField($form, 'finishDate', $thisProject->getFinishDate());
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
        $pageData = array('projectTitle' => $title);
    }

    public function successAction()
    {
        if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();
            $this->getResponse()->setHeader('Refresh', '3; URL=/projects');
        } else {
            $this->_redirect('/projects');
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
        $projectToBeDetailed = $this->InitProjectWithCheckedId($this->projectMapper);

        //  projectHeader
        //    id
        //    projectTitle
        //    clientName
        //    areaActivity
        //    ourResponsible
        //    responsibleAtClient
        //    overhead
        //    managementFee
        //    contractValue
        //    contractDate
        //

        $projectTitle = $projectToBeDetailed->getTitle();

        if (!isset($this->institutionMapper)) {
            $this->initInstitutionMapper();
        }

        $clientName = $this->view->translate('#(not defined)');
        if ($projectToBeDetailed->getClient() > 0) {
            $thisClient = $this->institutionMapper->findById($projectToBeDetailed->getClient());
            $clientName = $thisClient->GetShortName();
        }

        $obj = new C3op_Projects_AreaActivityTypes();
        $areaActivity = $obj->TitleForType($projectToBeDetailed->getAreaActivity());

        if (!isset($this->contactMapper)) {
            $this->initContactMapper();
        }

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
        $managementFee = $projectToBeDetailed->getManagementFee();
        $contractValue = C3op_Util_CurrencyDisplay::FormatCurrency($projectToBeDetailed->getValue());
        $contractDate = C3op_Util_DateDisplay::FormatDateToShow($projectToBeDetailed->getBeginDate());

        $projectHeader = array(
                'id'                  => $projectToBeDetailed->getId(),
                'projectTitle'        => $projectTitle,
                'clientName'          => $clientName,
                'areaActivity'        => $areaActivity,
                'ourResponsible'      => $ourResponsible,
                'responsibleAtClient' => $responsibleAtClient,
                'overhead'            => $overhead,
                'managementFee'       => $managementFee,
                'contractValue'       => $contractValue,
                'contractDate'        => $contractDate,
            );

        // receivablesList
        //   * id =>
        //      receivableTitle
        //      predictedDate
        //      realDate
        //      predictedValue
        //      realValue

        $receivablesList = array();

        $projectReceivables = $this->projectMapper->getAllReceivables($projectToBeDetailed);
        if (!isset($this->receivableMapper)) {
            $this->initReceivableMapper();
        }

        foreach ($projectReceivables as $id) {
            $theReceivable = $this->receivableMapper->findById($id);
            $productTitle = $theReceivable->getTitle();

            $validator = new C3op_Util_ValidDate();
            if ($validator->isValid($theReceivable->getPredictedDate())) {
                $predictedDate = C3op_Util_DateDisplay::FormatDateToShow($theReceivable->getPredictedDate());
            } else {
                $predictedDate = $this->view->translate("#(undefined)");
            }

            if ($validator->isValid($theReceivable->getRealDate())) {
                $realDate = C3op_Util_DateDisplay::FormatDateToShow($theReceivable->getRealDate());
            } else {
                $realDate = $this->view->translate("#(not received)");
            }

            $predictedValue = C3op_Util_CurrencyDisplay::FormatCurrency($theReceivable->getPredictedValue());
            $realValue = C3op_Util_CurrencyDisplay::FormatCurrency($theReceivable->getRealValue());

            $receivablesList[$id] = array(
                    'productTitle' => $productTitle,
                    'predictedDate' => $predictedDate,
                    'realDate' => $realDate,
                    'predictedValue' => $predictedValue,
                    'realValue' => $realValue,
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

        foreach ($projectOutlays as $id) {
            $theOutlay = $this->outlayMapper->findById($id);
            $actionId = $theOutlay->getAction();
            $theAction = $this->actionMapper->findById($actionId);
            $actionTitle = $theAction->getTitle();
            $payeeName = $this->view->translate("#Not defined");
            $payeeId = 0;
            if ($theOutlay->getTeamMember() > 0) {
                $theTeamMember = $this->teamMemberMapper->findById($theOutlay->getTeamMember());
                if ($theTeamMember->getContact() > 0) {
                    $theContact = $this->contactMapper->findById($theTeamMember->getContact());
                    $payeeId = $theContact->getId();
                    $payeeName = $theContact->getName();
                }
            }

            $predictedDate = C3op_Util_DateDisplay::FormatDateToShow($theOutlay->getPredictedDate());
            $realDate = C3op_Util_DateDisplay::FormatDateToShow('0000-00-00');
            $predictedValue = C3op_Util_CurrencyDisplay::FormatCurrency($theOutlay->getPredictedValue());
            $realValue = C3op_Util_CurrencyDisplay::FormatCurrency('0');

            $outlaysList[$id] = array(
                    'actionId' => $actionId,
                    'actionTitle' => $actionTitle,
                    'payeeId' => $payeeId,
                    'payeeName' => $payeeName,
                    'predictedDate' => $predictedDate,
                    'realDate' => $realDate,
                    'predictedValue' => $predictedValue,
                    'realValue' => $realValue,
                );
        }

        // teamMembersList
        //   * id =>
        //      staffName
        //      contactId
        //      positionDescription
        //      staffEmail
        //      staffPhoneNumber

        $teamMembersList = array();
        $projectTeam = $this->projectMapper->getAllTeamMembersContractedOrPredictedAt($projectToBeDetailed);
        if (!isset($this->teamMemberMapper)) {
            $this->initTeamMemberMapper();
        }

        foreach ($projectTeam as $id) {
            $theTeamMember = $this->teamMemberMapper->findById($id);
            $actionId = $theTeamMember->getAction();
            $theAction = $this->actionMapper->findById($actionId);
            $actionTitle = $theAction->getTitle();
            $staffName = $this->view->translate("#Not defined");
            $staffId = 0;
            $staffEmail = $this->view->translate("#Not defined");
            $staffPhoneNumber = $this->view->translate("#Not defined");
            if ($theTeamMember->getContact() > 0) {
                $theContact = $this->contactMapper->findById($theTeamMember->getContact());
                $staffId = $theContact->getId();
                $staffName = $theContact->getName();
                $staffEmail = "lorem@ipsum.com";
                $staffPhoneNumber = "21.2345-6789";
            }
            $positionDescription = $theTeamMember->getDescription();

            $teamMembersList[$id] = array(
                    'contactId'           => $staffId,
                    'positionDescription' => $positionDescription,
                    'staffName'           => $staffName,
                    'staffPhoneNumber'    => $staffPhoneNumber,
                    'staffEmail'          => $staffEmail,
                );
        }

        $objTree = new C3op_Projects_ProjectTree();
        $tree = $objTree->retrieveTree($projectToBeDetailed, $this->projectMapper, $this->actionMapper);

        $this->treeData = array();
        $this->fillDataTree($tree);

        $actionTreeList = $this->treeData;

        $pageData = array(
            'projectHeader' => $projectHeader,
            'receivablesList'  => $receivablesList,
            'outlaysList'   => $outlaysList,
            'actionsTree'   => $actionTreeList,
            'teamMembersList'     => $teamMembersList,

        );
        $this->view->pageData = $pageData;
    }

    public function receivablesAction()
    {
        $receivableMapper = new C3op_Projects_ReceivableMapper($this->db);

        $id = $this->checkIdFromGet();
        $thisProject = $this->projectMapper->findById($id);
        $receivablesIdList = $this->projectMapper->getAllReceivables($thisProject);
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
                $predictedValue = C3op_Util_CurrencyDisplay::FormatCurrency($thisReceivable->getPredictedValue());
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
                'editLink' => '/projects/receivable/edit/?id=' . $receivableId   ,
            );
        }

        if ($receivablesTotalValue == $thisProject->getValue()) {
            $projectValue = C3op_Util_CurrencyDisplay::FormatCurrency($receivablesTotalValue) . " (OK)";
        } else {
            $projectValue = "Valor do Projeto: " . C3op_Util_CurrencyDisplay::FormatCurrency($thisProject->getValue());
            $projectValue .= " Total dos recebimentos:" .  C3op_Util_CurrencyDisplay::FormatCurrency($receivablesTotalValue) . " (?)";

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
            $actionTitle = $thisAction->getTitle();
            $actionValue = C3op_Util_CurrencyDisplay::FormatCurrency(
                               $this->actionMapper->getContractedValueJustForThisAction($thisAction)
                           );

            $payablesList[$actionId] = array(
                'actionId'       => $actionId,
                'actionTitle'    => $actionTitle,
                'actionValue'    => $actionValue,
            );
        }

        $this->view->payablesList = $payablesList;

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

    private function initOutlayMapper()
    {
        if (!isset($this->outlayMapper)) {
            $this->outlayMapper = new C3op_Projects_OutlayMapper($this->db);
        }
    }

    private function initReceivableMapper()
    {
        if (!isset($this->receivableMapper)) {
            $this->receivableMapper = new C3op_Projects_ReceivableMapper($this->db);
        }
    }

    private function initTeamMemberMapper()
    {
         $this->teamMemberMapper = new C3op_Projects_TeamMemberMapper($this->db);
    }

    private function initInstitutionMapper()
    {
         $this->institutionMapper = new C3op_Register_InstitutionMapper($this->db);
    }

    private function initContactMapper()
    {
         $this->contactMapper = new C3op_Register_ContactMapper($this->db);
    }

    private function InitProjectWithCheckedId(C3op_Projects_ProjectMapper $mapper)
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
        throw new C3op_Projects_ProjectException("Invalid Project Id from Get");

    }

    private function buildSpecialActionLabel(C3op_Projects_Action $action)
    {
        $isSpecialAction = false;
        $separator = "";
        if ($action->getMilestone()) {
            $milestone = "*";
            $separator = " ";
            $isSpecialAction = true;
        } else {
            $milestone = "";
        }

        if ($action->getRequirementForReceiving()) {
            $isSpecialAction = true;
            $requirementForReceiving = $separator . '$';
        } else {
            $requirementForReceiving = "";
        }

        if ($isSpecialAction) {
            $specialAction = "($milestone$requirementForReceiving)";
        } else {
            $specialAction = "";
        }

        return $specialAction;
    }

    private function setDateValueToFormField(C3op_Form_ProjectCreate $form, $fieldName, $value)
    {
        $field = $form->getElement($fieldName);
        if ($value != '0000-00-00')  {
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

    private function outlayAsAParcel(C3op_Projects_Outlay $outlay)
    {
        $teamMemberId = $outlay->getTeamMember();
        if (!isset($this->teamMemberMapper)) {
            $this->teamMemberMapper = new C3op_Projects_TeamMemberMapper($this->db);
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
}