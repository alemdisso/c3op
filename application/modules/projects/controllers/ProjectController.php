<?php

class Projects_ProjectController extends Zend_Controller_Action
{
    private $projectMapper;
    private $actionMapper;
    private $db;
    private $detailProductDepth;
    private $detailProductBrood;
    private $detailProductBreeds;
    private $institutionMapper;
    private $contactMapper;
    private $outlayMapper;
    private $humanResourceMapper;

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
                $this->view->form = $form;
            }
        } else {
            // GET
            $thisProject = $this->InitProjectWithCheckedId($this->projectMapper);
            $id = $this->checkIdFromGet();
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $id);
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'title', $thisProject->getTitle());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'client', $thisProject->getClient());
            $this->populateClientField($form, $thisProject->getClient());
            $this->populateOurResponsibleField($form, $thisProject->getOurResponsible());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'responsibleAtClient', $thisProject->getResponsibleAtClient());
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

    public function errorEditAction()
    {
        $flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $flashMessenger->setNamespace('messages');
        $this->view->messages = $flashMessenger->getMessages();
        $flashMessenger->addMessage('Id Inválido');
    }

    public function detailAction()
    {

        if (!isset($this->projectMapper)) {
            $this->initProjectMapper();
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
        $theClient = $this->institutionMapper->findById($projectToBeDetailed->getClient());
        $clientName = $theClient->getShortName();

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
                'id'           => $projectToBeDetailed->getId(),
                'projectTitle'   => $projectTitle,
                'clientName'   => $theClient->getName(),
                'areaActivity' => $areaActivity,
                'ourResponsible' => $ourResponsible,
                'responsibleAtClient' => $responsibleAtClient,
                'overhead' => $overhead,
                'managementFee' => $managementFee,
                'contractValue' => $contractValue,
                'contractDate' => $contractDate,
            );

        // productsList
        //   * id =>
        //      productTitle
        //      predictedDate
        //      realDate
        //      predictedValue
        //      realValue

        $productsList = array();

        $projectProducts = $this->projectMapper->getAllProductsOf($projectToBeDetailed);
        if (!isset($this->actionMapper)) {
            $this->initActionMapper();
        }

        foreach ($projectProducts as $id) {
            $theAction = $this->actionMapper->findById($id);
            $productTitle = $theAction->getTitle();

            $predictedDate = C3op_Util_DateDisplay::FormatDateToShow($theAction->getPredictedFinishDate());
            $realDate = C3op_Util_DateDisplay::FormatDateToShow($theAction->getRealFinishDate());
            $predictedValue = C3op_Util_CurrencyDisplay::FormatCurrency(123.450);
            $realValue = C3op_Util_CurrencyDisplay::FormatCurrency(67.89);

            $productsList[$id] = array(
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
        if (!isset($this->humanResourceMapper)) {
            $this->initHumanResourceMapper();
        }

        foreach ($projectOutlays as $id) {
            $theOutlay = $this->outlayMapper->findById($id);
            $actionId = $theOutlay->getAction();
            $theAction = $this->actionMapper->findById($actionId);
            $actionTitle = $theAction->getTitle();
            $payeeName = $this->view->translate("#Not defined");
            $payeeId = 0;
            if ($theOutlay->getHumanResource() > 0) {
                $theHumanResource = $this->humanResourceMapper->findById($theOutlay->getHumanResource());
                if ($theHumanResource->getContact() > 0) {
                    $theContact = $this->contactMapper->findById($theHumanResource->getContact());
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




        $linkReceivables = $this->manageReceivablesLink($projectToBeDetailed);
        $linkPayables = $this->managePayablesLink($projectToBeDetailed);
        $linkUnacknowledged = $this->manageUnacknowledgedLink($projectToBeDetailed);
        $linkTree = $this->manageTreeLink($projectToBeDetailed);

        $projectProducts = $this->projectMapper->getAllProductsOf($projectToBeDetailed);
        $actionsList = array();
        reset ($actionsList);
        $actionMapper = new C3op_Projects_ActionMapper($this->db);
        foreach ($projectProducts as $actionId) {
            $this->detailProductBreeds = array();
            $this->detailProductBrood = 0;
            $this->detailProductDepth = 0;
            $thisAction = $actionMapper->findById($actionId);
            $immediateBreed = $actionMapper->getActionsSubordinatedTo($thisAction);
            if (count($immediateBreed) > 0) {
                $broodMessage = count($immediateBreed) . " ações diretamente subordinadas";
                if (count($immediateBreed)== 1) {
                    $broodMessage = count($immediateBreed) . " ação diretamente subordinada";
                }
            } else {
                $broodMessage = "sem ações diretamente subordinadas";
            }
            $actionTitle =  sprintf("<a href=/projects/action/detail/?id=%d>%s</a>", $actionId, $thisAction->getTitle());
            $specialActionLabel = $this->buildSpecialActionLabel($thisAction);
            $actionsList[$actionId] = array(
                'title' => $actionTitle,
                'depth' => $this->detailProductDepth,
                'brood' => $broodMessage,
                'specialAction' => $specialActionLabel,
                'editLink' => '/projects/action/edit/?id=' . $actionId   ,
            );
        }

        $projectInfo = array(
            // ### campos dos dados gerais do projeto
            'client' => '###CNSeg',
            'overhead' => '###20',
            'area' => '###Desenvolvimento local e microempreendimento',
            'managementFee' => '###0',
            'ourResponsible' => '###Manuel Thedim',
            'responsibleAtClient' => '###Ricardo Tavares',
            // ### campos da guia Recebimentos
            'linkReceivable' => '#',   // ### link para os detalhes do recebimento
            'receivableTitle' => '###Pesquisa Qualitativa',
            'receivablePredictedDate' => '###11/12/2012',
            'receivableRealDate' => '###11/12/2012',
            'receivablePredictedValue' => '###35.400,00',
            'receivableRealValue' => '###35.400,00',
            'receivableEditLink' => '#',   // ### link para o form de edição do recebimento
            'receivableConfirmationLink' => '#',   // ### link para a confirmação do recebimento
            // ### campos da guia Pagamentos
            'linkOutlay' => '#',   // ### link para os detalhes do desembolso
            'outlayActionTitle' => '###Projeto executivo',   // ### título do desembolso
            'linkSupplier' => '#',   // ### link para o fornecedor
            'outlayActionSupplier' => '###Kelly Miranda',
            'outlayPredictedDate' => '###11/12/2012',
            'outlayRealDate' => '###11/12/2012',
            'outlayPredictedValue' => '###5.400,00',
            'outlayRealValue' => '###5.400,00',
            'outlayEditLink' => '#',   // ### link para o form de edição do desembolso
            'outlayConfirmationLink' => '#',   // ### link para a confirmação do desembolso
            // ### campos da guia Equipe
            'linkHumanResource' => '#',   // ### link para os detalhes do contato
            'humanResourceName' => '###Kelly Miranda',   // ### nome do contato
            'humanResourceFunction' => '###Controller',
            'humanResourceEmail' => '###kelly@iets.org.br',
            'humanResourcePhone' => '###(21) 9999-9999',
            'humanResourceEditLink' => '#',   // ### link para o form de edição do contato

            'title' => $projectToBeDetailed->getTitle(),
            'editLink' => '/projects/project/edit/?id=' . $projectToBeDetailed->getId(),
            'linkReceivables' => $linkReceivables,
            'linkPayables' => $linkPayables,
            'linkUnacknowledged' => $linkUnacknowledged,
            'linkTree' => $linkTree,
            'beginDate' => C3op_Util_DateDisplay::FormatDateToShow($projectToBeDetailed->getBeginDate()),
            'value' => C3op_Util_CurrencyDisplay::FormatCurrency($projectToBeDetailed->getValue()),
            'linkActionCreate' => '/projects/action/create/?project=' . $projectToBeDetailed->getId(),
            'actionsList' => $actionsList,
        );

        $pageData = array(
            'projectHeader' => $projectHeader,
            'productsList' => $productsList,
            'outlaysList' => $outlaysList,

            'projectInfo'   => $projectInfo,
        );
        $this->view->pageData = $pageData;
    }

    public function treeAction()
    {
        $this->initProjectMapper();
        $project =  $this->InitProjectWithCheckedId($this->projectMapper);
        $this->initActionMapper();

        $objTree = new C3op_Projects_ProjectTree();
        $tree = $objTree->retrieveTree($project, $this->projectMapper, $this->actionMapper);

        $this->treeData = array();
        $this->fillDataTree($tree);


        $this->view->projectTree = $tree;
        $this->view->treeData = $this->treeData;

    }

    public function outlaysAction()
    {
        $id = $this->checkIdFromGet();
        $thisProject = $this->projectMapper->findById($id);

        $this->initOutlayMapper();
        $list = $this->projectMapper->getAllOutlaysRelatedToDoneActions($thisProject);

        $outlaysList = array();
        reset ($list);
        foreach ($list as $id) {
            $thisOutlay = $this->outlayMapper->findById($id);

            $humanResourceId = $thisOutlay->getHumanResource();
            if (!isset($this->humanResourceMapper)) {
                $this->humanResourceMapper = new C3op_Projects_HumanResourceMapper($this->db);
            }
            $outlayHumanResource = $this->humanResourceMapper->findById($humanResourceId);
            $listOutlaysForHumanResource = $this->humanResourceMapper->getAllOutlays($outlayHumanResource);
            $totalParcels = count($listOutlaysForHumanResource);

            $parcels = $this->outlayAsAParcel($thisOutlay);
            $description = $outlayHumanResource->getDescription();
            $contactId = $outlayHumanResource->getContact();
            if ($contactId) {
                if (!isset($this->contactMapper)) {
                    $this->contactMapper = new C3op_Register_ContactMapper($this->db);
                }
                $outlayContact = $this->contactMapper->findById($contactId);
                $name = $outlayContact->getName();
            } else {
                $name = "(indefinido)";
            }
            $this->initActionMapper();
            $outlayAction = $this->actionMapper->findById($thisOutlay->getAction());
            $actionTitle = $outlayAction->getTitle();

            $predictedDate = C3op_Util_DateDisplay::FormatDateToShow($thisOutlay->getPredictedDate());
            $predictedValue = C3op_Util_CurrencyDisplay::FormatCurrency($thisOutlay->getPredictedValue());



            $outlaysList[$id] = array(
                'name'           => $name,
                'description'    => $description,
                'parcels'        => $parcels,
                'actionTitle'    => $actionTitle,
                'predictedDate'  => $predictedDate,
                'predictedValue' => $predictedValue,
            );
        }

        $this->view->outlaysList = $outlaysList;

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
                $title = "(#$receivablesCounter)";
            }

            $validator = new C3op_Util_ValidDate();
            if ($validator->isValid($thisReceivable->getPredictedDate())) {
                $predictedDate = C3op_Util_DateDisplay::FormatDateToShow($thisReceivable->getPredictedDate());
            } else {
                $predictedDate = "(data desconhecida)";
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
            'title' => $thisProject->getTitle(),
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

    private function initHumanResourceMapper()
    {
         $this->humanResourceMapper = new C3op_Projects_HumanResourceMapper($this->db);
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

    private function manageReceivablesLink(C3op_Projects_Project $project)
    {
        $receivablesIdList = $this->projectMapper->getAllReceivables($project);
        if (count($receivablesIdList) > 0) {
            $linkReceivables = '/projects/project/receivables/?id=' . $project->getId();
        } else {
            $linkReceivables = "";
        }
        return $linkReceivables;

    }

    private function managePayablesLink(C3op_Projects_Project $project)
    {
        $payablesLink = $this->projectMapper->getAllDoneActions($project);
        if (count($payablesLink) > 0) {
            $linkReceivables = '/projects/project/payables/?id=' . $project->getId();
        } else {
            $linkReceivables = "";
        }
        return $linkReceivables;

    }

    private function manageUnacknowledgedLink(C3op_Projects_Project $project)
    {
        $linkUnacknowledged = '/projects/project/unacknowledged/?id=' . $project->getId();
        return $linkUnacknowledged;

    }

    private function manageTreeLink(C3op_Projects_Project $project)
    {
        $linkTree = '/projects/project/tree/?id=' . $project->getId();
        return $linkTree;

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

    private function outlayAsAParcel(C3op_Projects_Outlay $outlay)
    {
        $humanResourceId = $outlay->getHumanResource();
        if (!isset($this->humanResourceMapper)) {
            $this->humanResourceMapper = new C3op_Projects_HumanResourceMapper($this->db);
        }
        $outlayHumanResource = $this->humanResourceMapper->findById($humanResourceId);
        $listOutlaysForHumanResource = $this->humanResourceMapper->getAllOutlays($outlayHumanResource);
        $totalParcels = count($listOutlaysForHumanResource);

        $parcelsCount = 0;
        foreach($listOutlaysForHumanResource as $parcelId) {
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
        $this->initActionMapper();
        foreach ($tree as $id => $subTree) {
            $loopAction = $this->actionMapper->findById($id);
            $data = array();
            $data["title"] = $loopAction->getTitle();

            $contract = new C3op_Projects_ActionContracting($loopAction, $this->actionMapper);
            if ($contract->isContracted()) {
                $data["contracted"] = "contratada";
            } else {
                $data["contracted"] = "";
            }

            $data["value"] = C3op_Util_CurrencyDisplay::FormatCurrency(
                    $this->actionMapper->getContractedValueForActionTree($loopAction));

            $done = new C3op_Projects_ActionDone($loopAction);
            if ($done->isDone()) {
                $data["done"] = "finalizada";
            } else {
                $data["done"] = "";
            }

            $this->treeData[$id] = $data;


            $this->fillDataTree($subTree);
        }
    }

}