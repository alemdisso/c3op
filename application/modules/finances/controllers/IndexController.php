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
    private $teamMemberMapper;

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
        //      areaActivity
        //      status
        //      physicalProgress
        //      payedPercentage
        //      receivedPercentage




        $list = $this->projectMapper->getAllIds();
        $projectsList = array();
        reset ($list);
        $this->institutionMapper = new C3op_Register_InstitutionMapper($this->db);
        foreach ($list as $id) {
            $thisProject = $this->projectMapper->findById($id);

            $clientName = $this->view->translate('#(not defined)');
            if ($thisProject->getClient() > 0) {
                $thisClient = $this->institutionMapper->findById($thisProject->getClient());
                $clientName = $thisClient->GetShortName();
            }

            $obj = new C3op_Projects_AreaActivityTypes();
            $areaActivity = $obj->TitleForType($thisProject->getAreaActivity());

            $obj = new C3op_Projects_ProjectStatusTypes();
            $status = $obj->TitleForType($thisProject->getStatus());

            $actionsCount = count($this->projectMapper->GetAllActions($thisProject));

            $contracts = $this->projectMapper->getAllContracts($thisProject);
            if (count($contracts)) {
                $hasContract = true;
            } else {
                $hasContract = false;
            }

           $this->receivableMapper = new C3op_Finances_ReceivableMapper($this->db);
           $obj = new C3op_Finances_ProjectFinancialProgress($thisProject, $this->receivableMapper);
           $receivedPercentage = $obj->progress();



            $projectsList[$id] = array(
                'projectName'        => $thisProject->GetShortTitle(),
                'clientName'         => $clientName,
                'areaActivity'       => $areaActivity,
                'status'             => $status,
                'physicalProgress'   => '[#12%]',
                'payedPercentage'    => '[#10%]',
                'receivedPercentage' => $receivedPercentage,
                'hasContract'       => $hasContract,
            );


            }

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
                'projectsList' => $projectsList,
                'payablesList' => $payablesList,
                'outlaysList'  => $outlaysList,
            );

        $this->view->pageData = $pageData;
        $this->view->projectsList = $projectsList;

        $this->view->createProjectLink = "/projects/project/create";


    }

    private function fetchOutlayData(C3op_Finances_Outlay $outlay)
    {

        if (!isset($this->teamMemberMapper)) {
            $this->initTeamMemberMapper();
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
        if (!isset($this->projectMapper)) {
            $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
        }

        $outlayData = array();

        $actionId = $outlay->getAction();
        $theAction = $this->actionMapper->findById($actionId);
        $actionTitle = $theAction->getTitle();

        $thisProject = $this->projectMapper->findById($outlay->GetProject());
        $projectTitle = $thisProject->GetShortTitle();
        $projectId = $thisProject->GetId();



        $payeeName = $this->view->translate("#Not defined");
        $payeeId = 0;
        if ($outlay->getTeamMember() > 0) {
            $teamMemberId = $outlay->getTeamMember();
            $theTeamMember = $this->teamMemberMapper->findById($teamMemberId);

            $linkageId = null;


            if ($theTeamMember->getLinkage() > 0) {

                $theLinkage = $this->linkageMapper->findById($theTeamMember->getLinkage());
                $theContact = $this->contactMapper->findById($theLinkage->getContact());

                $payeeId = $theContact->getId();
                $payeeName = $theContact->getName();
                $linkageId = $theTeamMember->getLinkage();

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
            'linkageId'       => $linkageId,
            'teamMemberId'    => $teamMemberId,
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

        if (!isset($this->teamMemberMapper)) {
            $this->initTeamMemberMapper();
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
        if (!isset($this->projectMapper)) {
            $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
        }

        $outlayData = array();

        if ($outlay->GetTeamMember() > 0) {

            $teamMember = $this->teamMemberMapper->findById($outlay->GetTeamMember() );

            $payeeName = $this->view->translate("#(not defined)");
            $teamMemberId = null;
            $linkageId = null;

            if ($teamMember->getLinkage() > 0) {
                $teamMemberLinkage = $this->linkageMapper->findById($teamMember->getLinkage());
                $teamMemberContact = $this->contactMapper->findById($teamMemberLinkage->getContact());
                $payeeName = $teamMemberContact->GetName();
                $teamMemberId = $teamMember->Getid();
                $linkageId = $teamMember->getLinkage();
            }

            $teamMemberAction = $this->actionMapper->findById($teamMember->GetAction());
            $actionTitle = $teamMemberAction->GetTitle();
            $actionId = $teamMemberAction->GetId();

            $thisProject = $this->projectMapper->findById($teamMemberAction->GetProject());
            $projectTitle = $thisProject->GetShortTitle();
            $projectId = $thisProject->GetId();

            $currencyDisplay = new  C3op_Util_CurrencyDisplay();

            $predictedValue = $outlay->GetPredictedValue();
            if ($predictedValue === null) {
                $predictedValue = "0.00";
            }
            $predictedValue = $currencyDisplay->FormatCurrency($predictedValue);
            $totalValue = $currencyDisplay->FormatCurrency($teamMember->getValue());

            $actionStatus = $teamMemberAction->getStatus();
            $statusTypes = new C3op_Projects_ActionStatusTypes();
            $actionStatusLabel = $this->view->translate($statusTypes->TitleForType($actionStatus));


            $status = $teamMember->getStatus();
            if ($status == C3op_Resources_TeamMemberStatusConstants::STATUS_CONTRACTED) {
                $doesIt = new C3op_Resources_TeamMemberHasCredit($teamMember, $this->teamMemberMapper);
                if ($doesIt->hasCreditToPay()) {
                    $canNotifyOutlay = true;
                } else {
                    $canNotifyOutlay = false;
                }
            } else {
                $canNotifyOutlay = false;
            }




            $validator = new C3op_Util_ValidDate();
            if ($validator->isValid($teamMemberAction->getPredictedBeginDate())) {
                $predictedDate = C3op_Util_DateDisplay::FormatDateToShow($teamMemberAction->getPredictedBeginDate());
            } else{
                $predictedDate = $this->view->translate("#Undefined dates");
            }

            $outlayData = array(
                'payeeName'       => $payeeName,
                'payeeId'         => $teamMemberId,
                'actionTitle'     => $actionTitle,
                'actionId'        => $actionId,
                'linkageId'       => $linkageId,
                'teamMemberId'    => $teamMemberId,
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


    private function initProjectMapper()
    {
        if (!isset($this->projectMapper)) {
            $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
        }
    }

    private function initTeamMemberMapper()
    {
        if (!isset($this->teamMemberMapper)) {
            $this->teamMemberMapper = new C3op_Resources_TeamMemberMapper($this->db);
        }
    }



}