<?php

class Finances_ProjectController extends Zend_Controller_Action
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
            $this->_redirect('/finances' . $id);
        }
    }

    public function init()
    {
        $this->db = Zend_Registry::get('db');
        $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
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
        if (!isset($this->responsibleMapper)) {
            $this->initResponsibleMapper();
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

            if ($theOutlay->getResponsible() > 0) {
                $theResponsible = $this->responsibleMapper->findById($theOutlay->GetResponsible() );

                $responsibleId = null;
                $responsibleType = $theResponsible->getType();

                if (($responsibleType == C3op_Resources_ResponsibleTypeConstants::TYPE_TEAM_MEMBER)
                    && ($theResponsible->getContact() > 0)) {
                    $responsibleContact = $this->contactMapper->findById($theResponsible->getContact());
                    $payeeName = $responsibleContact->getName();
                    $responsibleId = $theResponsible->getId();
                    $payeeId = $theResponsible->getContact();
                }

                if (($responsibleType == C3op_Resources_ResponsibleTypeConstants::TYPE_OUTSIDE_SERVICE)
                    && ($theResponsible->getInstitution() > 0)) {
                    $responsibleInstitution = $this->institutionMapper->findById($theResponsible->getInstitution());
                    $payeeName = $responsibleInstitution->getShortName();
                    $responsibleId = $theResponsible->getId();
                    $payeeId = $theResponsible->getInstitution();
                }

                if ($payeeId > 0) {

                    $status = $theResponsible->getStatus();
                    if ($status == C3op_Resources_ResponsibleStatusConstants::STATUS_CONTRACTED) {
                        $doesIt = new C3op_Resources_ResponsibleHasCredit($theResponsible, $this->responsibleMapper);
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

        $productsList = $this->fillProductsList($projectToBeDetailed);


        $pageData = array(
            'projectHeader'        => $projectHeader,
            'messageToShow'        => $messageToShow,
            'receivablesList'      => $receivablesList,
            'outlaysList'          => $outlaysList,
            'productsList'         => $productsList,

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
            'linkDetail' => '/finances/project/detail/?id=' . $id   ,
            'projectValue' => $projectValue,
            'editLink' => '/projects/project/edit/?id=' . $id   ,
            'receivablesList' => $receivablesList,
        );

        $this->view->projectInfo = $projectInfo;
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

//   private function fillDataTree($tree)
//    {
//       // actionInfo
//       //   title
//       //   subordinatedTo
//       //   responsibleName
//       //   predictedBeginDate
//       //   realBeginDate
//       //   predictedFinishDate
//       //   realFinishDate
//       //
//
//       $this->initActionMapper();
//        foreach ($tree as $id => $subTree) {
//            $loopAction = $this->actionMapper->findById($id);
//            $data = array();
//            $data['title'] = $loopAction->getTitle();
//            $data['subordinatedTo'] = $loopAction->getSubordinatedTo();
//
//            if ($loopAction->getSupervisor()) {
//                $theContact = $this->contactMapper->findById($loopAction->getSupervisor());
//                $data['responsibleName'] = $theContact->getName();
//            } else {
//                $data['responsibleName'] = $this->view->translate("#Not defined");
//            }
//
//            $data['predictedBeginDate'] = C3op_Util_DateDisplay::FormatDateToShow($loopAction->getPredictedBeginDate());
//            $data['realBeginDate'] = C3op_Util_DateDisplay::FormatDateToShow($loopAction->getRealBeginDate());
//            $data['predictedFinishDate'] = C3op_Util_DateDisplay::FormatDateToShow($loopAction->getPredictedFinishDate());
//            $data['realFinishDate'] = C3op_Util_DateDisplay::FormatDateToShow($loopAction->getRealFinishDate());
//
//            $this->treeData[$id] = $data;
//
//            $this->fillDataTree($subTree);
//        }
//    }
//
//
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

    private function fillProductsList(C3op_Projects_Project $projectToBeDetailed)
    {


        // productsList
        //   * id =>
        //      productTitle
        //      receivableLabel
        //      receivableId
        //      totalValue
        //      totalContracted

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


            if ($theProduct->getRequirementForReceiving() > 0) {
                $theReceivable = $this->receivableMapper->findById($theProduct->getRequirementForReceiving());
                $requirementForReceiving = $theReceivable->getTitle();
                $receivableId = $theProduct->getRequirementForReceiving();
                $receivableDescription = $theReceivable->getTitle();
            } else {
                $requirementForReceiving = $this->view->translate("#(not a requirement)");
                $receivableId = null;
                $receivableDescription = "";
            }

            $statusTypes = new C3op_Projects_ActionStatusTypes();
            $status = $statusTypes->TitleForType($theProduct->getStatus());

            $actionValueObj = new C3op_Projects_ActionCost($theProduct,$this->actionMapper);
            $actionsBelow = new C3op_Projects_ActionsBelow($theProduct,$this->actionMapper);
            $currencyDisplay = new  C3op_Util_CurrencyDisplay();


            $rawTotalValue = $actionValueObj->individualBudgetValue();
            $totalValue = $currencyDisplay->FormatCurrency($rawTotalValue);



            $rawContractedValue = $actionValueObj->totalActionContractedValue($actionsBelow, new C3op_Resources_MaterialSupplyMapper);
            $contractedValue = $currencyDisplay->FormatCurrency($rawContractedValue);

            $productsList[$id] = array(
                    'productTitle'            => $productTitle,
                    'status'                  => $status,
                    'receivableLabel'         => $receivableDescription,
                    'receivableId'            => $receivableId,
                    'totalValue'              => $totalValue,
                    'contractedValue'         => $contractedValue,
                );
        }






        return $productsList;

    }



}
