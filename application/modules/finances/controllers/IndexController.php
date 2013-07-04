<?php

class Finances_IndexController extends Zend_Controller_Action
{
    private $db;
    private $actionMapper;
    private $contactMapper;
    private $institutionMapper;
    private $linkageMapper;
    private $outlayMapper;
    private $projectMapper;
    private $responsibleMapper;
    private $receivableMapper;
    private $deliveryMapper;

    public function init()
    {

        $this->db = Zend_Registry::get('db');
        $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);

        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('list', 'html')
                    ->addActionContext('modify', 'html')
                    ->initContext();

   }

    public function indexAction()
    {

        // projectsList
        //   * id =>
        //      projectName
        //      projectId
        //      clientName
        //      status
        //      projectValue
        //      totalPaymentValues
        //      balance



        $this->initActionMapper();

        $activeProjectsList = $this->fillProjectsList();
        $allProjectsList = $this->fillProjectsList(true);

        $progressList = $this->fillProgressList();

        $this->outlayMapper = new C3op_Finances_OutlayMapper($this->db);

        $list = $this->outlayMapper->fetchAllOutlaysThatCanBePayed();
        $payablesList = array();

        foreach ($list as $id) {

            if (!isset($this->outlayMapper)) {
                $this->initOutlayMapper();
            }
            $thisOutlay = $this->outlayMapper->findById($id);

            $data = $this->fetchPayableData($thisOutlay);




            $payablesList[$id] = $data;

        }

        $list = $this->outlayMapper->fetchAllOutlaysFromAllActiveProjects();
        $outlaysList = array();

        foreach ($list as $id) {

            if (!isset($this->outlayMapper)) {
                $this->initOutlayMapper();
            }
            $thisOutlay = $this->outlayMapper->findById($id);

            $data = $this->fetchOutlayData($thisOutlay);

            $outlaysList[$id] = $data;

        }




        $pageData = array(
                'activeProjectsList' => $activeProjectsList,
                'payablesList'       => $payablesList,
                'progressList'       => $progressList,
                'outlaysList'        => $outlaysList,
                'allProjectsList'    => $allProjectsList,
            );

        $this->view->pageData = $pageData;
        //$this->view->projectsList = $activeProjectsList;

        //$this->view->createProjectLink = "/projects/project/create";


    }

    private function fetchOutlayData(C3op_Finances_Outlay $outlay)
    {

        if (!isset($this->responsibleMapper)) {
            $this->initResponsibleMapper();
        }
        if (!isset($this->linkageMapper)) {
            $this->initLinkageMapper();
        }
        if (!isset($this->contactMapper)) {
            $this->initContactMapper();
        }
        if (!isset($this->actionMapper)) {
            $this->initActionMapper();
        }


        $outlayData = array();

        $actionId = $outlay->getAction();
        $theAction = $this->actionMapper->findById($actionId);
        $actionTitle = $theAction->getTitle();

        $thisProject = $this->projectMapper->findById($outlay->GetProject());
        $projectTitle = $thisProject->GetShortTitle();
        $projectId = $thisProject->GetId();



        $payeeName = $this->view->translate("#Not defined");
        $payeeId = null;
        if ($outlay->getResponsible() > 0) {
            $responsibleId = $outlay->getResponsible();
            $theResponsible = $this->responsibleMapper->findById($responsibleId);


            $contactId = null;
            $institutionId = null;

            if (($theResponsible->getType() == C3op_Resources_ResponsibleTypeConstants::TYPE_TEAM_MEMBER)
                 && ($theResponsible->getContact() > 0)) {
                $responsibleContact = $this->contactMapper->findById($theResponsible->getContact());
                $payeeName = $responsibleContact->getName();
                $payeeId = $responsibleContact->getId();
                $responsibleId = $theResponsible->getId();
            }

            if (($theResponsible->getType() == C3op_Resources_ResponsibleTypeConstants::TYPE_OUTSIDE_SERVICE)
                 && ($theResponsible->getInstitution() > 0)) {
                $responsibleInstitution = $this->institutionMapper->findById($theResponsible->getInstitution());
                $payeeName = $responsibleInstitution->getShortName();
                $responsibleId = $theResponsible->getId();
                $payeeId = $theResponsible->getInstitution();
            }



            if ($payeeId) {


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

        if ($validator->isValid($outlay->getRealDate())) {
            $realDate = C3op_Util_DateDisplay::FormatDateToShow($outlay->getRealDate());
        } else {
            $realDate = $this->view->translate('#(undefined)');
        }

        if ($validator->isValid($outlay->getPredictedDate())) {
            $predictedDate = C3op_Util_DateDisplay::FormatDateToShow($outlay->getPredictedDate());
        } else {
            $predictedDate = $this->view->translate('#(undefined)');
        }
        $currencyDisplay = new  C3op_Util_CurrencyDisplay();
        $predictedValue = $currencyDisplay->FormatCurrency($outlay->getPredictedValue());
        $realValue = $currencyDisplay->FormatCurrency($outlay->getRealValue());

        $outlayData = array(
            'payeeName'       => $payeeName,
            'payeeId'         => $payeeId,
            'actionTitle'     => $actionTitle,
            'actionId'        => $actionId,
            'responsibleId'   => $responsibleId,
            'projectTitle'    => $projectTitle,
            'projectId'       => $projectId,
            'predictedDate'   => $predictedDate,
            'realDate'        => $realDate,
            'predictedValue'  => $predictedValue,
            'realValue'       => $realValue,
            'canNotifyOutlay' => $canNotifyOutlay,
        );

        return $outlayData;
    }


    private function fetchPayableData(C3op_Finances_Outlay $outlay)
    {

        if (!isset($this->responsibleMapper)) {
            $this->initResponsibleMapper();
        }
        if (!isset($this->linkageMapper)) {
            $this->initLinkageMapper();
        }
        if (!isset($this->contactMapper)) {
            $this->initContactMapper();
        }
        if (!isset($this->actionMapper)) {
            $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
        }


        $outlayData = array();

        if ($outlay->GetResponsible() > 0) {

            $responsible = $this->responsibleMapper->findById($outlay->GetResponsible() );

            $payeeName = $this->view->translate("#(not defined)");
            $responsibleId = null;
            $contactId = null;
            $institutionId = null;

            $responsibleType = $responsible->getType();

            if (($responsibleType == C3op_Resources_ResponsibleTypeConstants::TYPE_TEAM_MEMBER)
                 && ($responsible->getContact() > 0)) {
                $responsibleContact = $this->contactMapper->findById($responsible->getContact());
                $payeeName = $responsibleContact->getName();
                $responsibleId = $responsible->getId();
                $contactId = $responsible->getContact();
            }

            if (($responsibleType == C3op_Resources_ResponsibleTypeConstants::TYPE_OUTSIDE_SERVICE)
                 && ($responsible->getInstitution() > 0)) {
                $responsibleInstitution = $this->institutionMapper->findById($responsible->getInstitution());
                $payeeName = $responsibleInstitution->getShortName();
                $responsibleId = $responsible->getId();
                $institutionId = $responsible->getInstitution();
            }

            $responsibleAction = $this->actionMapper->findById($responsible->GetAction());
            $actionTitle = $responsibleAction->GetTitle();
            $actionId = $responsibleAction->GetId();

            $thisProject = $this->projectMapper->findById($responsibleAction->GetProject());
            $projectTitle = $thisProject->GetShortTitle();
            $projectId = $thisProject->GetId();

            $currencyDisplay = new  C3op_Util_CurrencyDisplay();

            $predictedValue = $outlay->GetPredictedValue();
            if ($predictedValue === null) {
                $predictedValue = "0.00";
            }
            $predictedValue = $currencyDisplay->FormatCurrency($predictedValue);
            $totalValue = $currencyDisplay->FormatCurrency($responsible->getContractedValue());

            $actionStatus = $responsibleAction->getStatus();
            $statusTypes = new C3op_Projects_ActionStatusTypes();
            $actionStatusLabel = $this->view->translate($statusTypes->TitleForType($actionStatus));


            $status = $responsible->getStatus();
            if ($status == C3op_Resources_ResponsibleStatusConstants::STATUS_CONTRACTED) {
                $doesIt = new C3op_Resources_ResponsibleHasCredit($responsible, $this->responsibleMapper);
                if ($doesIt->hasCreditToPay()) {
                    $canNotifyOutlay = true;
                } else {
                    $canNotifyOutlay = false;
                }
            } else {
                $canNotifyOutlay = false;
            }




            $validator = new C3op_Util_ValidDate();
            if ($validator->isValid($responsibleAction->getPredictedBeginDate())) {
                $predictedDate = C3op_Util_DateDisplay::FormatDateToShow($responsibleAction->getPredictedBeginDate());
            } else{
                $predictedDate = $this->view->translate("#Undefined dates");
            }

            $outlayData = array(
                'payeeName'       => $payeeName,
                'payeeId'         => $responsibleId,
                'institutionId'   => $institutionId,
                'contactId'       => $contactId,
                'actionTitle'     => $actionTitle,
                'actionId'        => $actionId,
                'responsibleId'   => $responsibleId,
                'responsibleType' => $responsibleType,
                'projectTitle'    => $projectTitle,
                'projectId'       => $projectId,
                'predictedValue'  => $predictedValue,
                'predictedDate'   => $predictedDate,
                'status'          => $actionStatusLabel,
                'canNotifyOutlay' => $canNotifyOutlay,
            );

            return $outlayData;
        } else {
            throw new C3op_Finances_OutlayException(_("#It needs a team member to be payed."));
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
         $this->contactMapper = new C3op_Register_ContactMapper($this->db);
    }

    private function initLinkageMapper()
    {
         $this->linkageMapper = new C3op_Register_LinkageMapper($this->db);
    }


    private function initOutlayMapper()
    {
        if (!isset($this->outlayMapper)) {
            $this->outlayMapper = new C3op_Finances_OutlayMapper($this->db);
        }
    }


   private function initInstitutionMapper()
    {
         $this->institutionMapper = new C3op_Register_InstitutionMapper($this->db);
    }



    private function initResponsibleMapper()
    {
        if (!isset($this->responsibleMapper)) {
            $this->responsibleMapper = new C3op_Resources_ResponsibleMapper($this->db);
        }
    }

    private function initDeliveryMapper()
    {
        if (!isset($this->deliveryMapper)) {
            $this->deliveryMapper = new C3op_Projects_DeliveryMapper($this->db);
        }
    }


   private function initReceivableMapper()
    {
        if (!isset($this->receivableMapper)) {
            $this->receivableMapper = new C3op_Finances_ReceivableMapper($this->db);
        }
    }



    private function fillProjectsList($all = false)
    {


        $this->initInstitutionMapper();
        $this->initContactMapper();
        $this->initReceivableMapper();
        $this->initDeliveryMapper();
        $this->initActionMapper();


        if ($all) {
            $projects = $this->projectMapper->getAllProjects();
        } else {
            $projects = $this->projectMapper->getAllActiveProjects();
        }
        $data = array();


        $data = array();
        reset ($projects);
        $this->institutionMapper = new C3op_Register_InstitutionMapper($this->db);
        foreach ($projects as $id) {
            $loopProject = $this->projectMapper->findById($id);

            $clientName = $this->view->translate('#(not defined)');
            if ($loopProject->getClient() > 0) {
                $thisClient = $this->institutionMapper->findById($loopProject->getClient());
                $clientName = $thisClient->GetShortName();
            }

            $obj = new C3op_Projects_ProjectStatusTypes();
            $status = $obj->TitleForType($loopProject->getStatus());

            $contracts = $this->projectMapper->getAllContracts($loopProject);
            if (count($contracts)) {
                $hasContract = true;
            } else {
                $hasContract = false;
            }

            $currencyDisplay = new  C3op_Util_CurrencyDisplay();
            $projectValue = $loopProject->getValue();
            $formatedProjectValue = $currencyDisplay->FormatCurrency($projectValue);

            $allActionsInProject = $this->projectMapper->GetAllActions($loopProject);
            $totalProjectCost = 0.0;


            $materialSupplierMapper = new C3op_Resources_MaterialSupplyMapper($this->db);
            foreach ($allActionsInProject as $actionId) {
                $loopAction = $this->actionMapper->findById($actionId);
                $actionValueObj = new C3op_Projects_ActionCost($loopAction,$this->actionMapper);
                $currentActionValue = $actionValueObj->individualCurrentValue();
                $materialCost = $materialSupplierMapper->getMaterialSuppliesValueJustForThisAction($loopAction);
                $totalProjectCost += $currentActionValue;
                $totalProjectCost += $materialCost;
            }



            $formatedTotalCost = $currencyDisplay->FormatCurrency($totalProjectCost);

            $balanceValue = $projectValue - $totalProjectCost;
            if ($balanceValue < 0.0) {
                $negativeBalance = true;
            } else {
                $negativeBalance = false;
            }

            $formatedBalanceValue = $currencyDisplay->FormatCurrency($balanceValue);



//
//           $this->receivableMapper = new C3op_Finances_ReceivableMapper($this->db);
//           $obj = new C3op_Finances_ProjectFinancialProgress($thisProject, $this->receivableMapper);
//           $receivedPercentage = $obj->progress();



            $data[$id] = array(
                'projectName'        => $loopProject->GetShortTitle(),
                'clientName'         => $clientName,
                'status'             => $status,
                'projectValue'       => $formatedProjectValue,
                'totalPaymentValues' => $formatedTotalCost,
                'balance'            => $formatedBalanceValue,
                'negativeBalance'    => $negativeBalance,
                'hasContract'        => $hasContract,
            );


            }

            return $data;


    }

    private function fillProgressList($all = false)
    {


        $this->initInstitutionMapper();
        $this->initContactMapper();
        $this->initReceivableMapper();
        $this->initDeliveryMapper();
        $this->initActionMapper();
        $this->initOutlayMapper();



        if ($all) {
            $projects = $this->projectMapper->getAllProjects();
        } else {
            $projects = $this->projectMapper->getAllActiveProjects();
        }
        $data = array();


        $data = array();
        reset ($projects);
        $this->institutionMapper = new C3op_Register_InstitutionMapper($this->db);
        foreach ($projects as $id) {
            $loopProject = $this->projectMapper->findById($id);

            $clientName = $this->view->translate('#(not defined)');
            if ($loopProject->getClient() > 0) {
                $thisClient = $this->institutionMapper->findById($loopProject->getClient());
                $clientName = $thisClient->GetShortName();
            }

            $obj = new C3op_Projects_ProjectStatusTypes();
            $status = $obj->TitleForType($loopProject->getStatus());

            $contracts = $this->projectMapper->getAllContracts($loopProject);
            if (count($contracts)) {
                $hasContract = true;
            } else {
                $hasContract = false;
            }

            $currencyDisplay = new  C3op_Util_CurrencyDisplay();
            $calculator = new C3op_Finances_ProjectFinancialProgress($loopProject, $this->receivableMapper);
            $received = $calculator->totalReceivedValue();
            $formatedReceivedValue = $currencyDisplay->FormatCurrency($received);

//            $allActionsInProject = $this->projectMapper->GetAllActions($loopProject);
//            $totalProjectCost = 0.0;
//
//
//            $materialSupplierMapper = new C3op_Resources_MaterialSupplyMapper($this->db);
//            foreach ($allActionsInProject as $actionId) {
//                $loopAction = $this->actionMapper->findById($actionId);
//                $actionValueObj = new C3op_Projects_ActionCost($loopAction,$this->actionMapper);
//                $currentActionValue = $actionValueObj->individualCurrentValue();
//                $materialCost = $materialSupplierMapper->getMaterialSuppliesValueJustForThisAction($loopAction);
//                $totalProjectCost += $currentActionValue;
//                $totalProjectCost += $materialCost;
//            }
//
//
//
//            $formatedTotalCost = $currencyDisplay->FormatCurrency($totalProjectCost);



            $totalPayed = $calculator->totalPayedValue($this->outlayMapper);
            $formatedPayedValue = $currencyDisplay->FormatCurrency($totalPayed);
            $balanceValue = $received - $totalPayed;
            if ($balanceValue < 0.0) {
                $negativeBalance = true;
            } else {
                $negativeBalance = false;
            }

            $formatedBalanceValue = $currencyDisplay->FormatCurrency($balanceValue);



//
//           $this->receivableMapper = new C3op_Finances_ReceivableMapper($this->db);
//           $obj = new C3op_Finances_ProjectFinancialProgress($thisProject, $this->receivableMapper);
//           $receivedPercentage = $obj->progress();



            $data[$id] = array(
                'projectName'        => $loopProject->GetShortTitle(),
                'clientName'         => $clientName,
                'status'             => $status,
                'receivedValue'      => $formatedReceivedValue,
                'payedValue'         => $formatedPayedValue,
                'balance'            => $formatedBalanceValue,
                'negativeBalance'    => $negativeBalance,
                'hasContract'        => $hasContract,
            );


            }

            return $data;


    }

}