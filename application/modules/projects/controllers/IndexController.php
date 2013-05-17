<?php

class Projects_IndexController extends Zend_Controller_Action
{
    private $db;
    private $projectMapper;
    private $actionMapper;
    private $institutionMapper;
    private $receivableMapper;
    private $deliveryMapper;

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
        $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);

        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('list', 'html')
                    ->addActionContext('modify', 'html')
                    ->initContext();

   }

    public function allProjectsAction()
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


            $projectsList[$id] = array(
                'projectName'        => $thisProject->GetShortTitle(),
                'clientName'         => $clientName,
                'areaActivity'       => $areaActivity,
                'status'             => $status,
                'physicalProgress'   => '[#12%]',
                'payedPercentage'    => '[#10%]',
                'receivedPercentage' => '[#11%]',
                'hasContract'       => $hasContract,
            );

            /* temp */

//            $doesIt = new C3op_Projects_ProjectSeemsToBeInExecution($thisProject);
//            if ($doesIt->seemsToBeInExecution()) {
//                $thisProject->setStatus(C3op_Projects_ProjectStatusConstants::STATUS_EXECUTION);
//                $this->projectMapper->update($thisProject);
//            } else {
//                $doesIt = new C3op_Projects_ProjectSeemsToBeFinished($thisProject);
//                if ($doesIt->seemsToBeFinished()) {
//                    $thisProject->setStatus(C3op_Projects_ProjectStatusConstants::STATUS_FINISHED);
//                    $this->projectMapper->update($thisProject);
//                } else {
//                    $thisProject->setStatus(C3op_Projects_ProjectStatusConstants::STATUS_PLANNING);
//                    $this->projectMapper->update($thisProject);
//                }
//            }
//
//            if ($hasContract) {
////                $projectsList[$id]['projectName'] = $thisProject->GetShortTitle() . "!!!";
//            } else {
//                $doesIt = new C3op_Projects_ProjectSeemsToBeContracted($thisProject);
//                if ($doesIt->seemsToBeContracted()) {
//                    $this->projectMapper->createContract($thisProject);
//                } else {
////                    $projectsList[$id]['projectName'] = $thisProject->GetShortTitle() . "!?!";
//                }
//            }



            }


        $pageData = array(
                'projectsList' => $projectsList
            );

        $this->view->pageData = $pageData;
        $this->view->projectsList = $projectsList;

        $this->view->createProjectLink = "/projects/project/create";


    }


    public function activeAction() {

        $user = Zend_Registry::get('user');
        $test = new C3op_Access_UserCanSeeFinances($user);
        if ($test->can()) {
            $canSeeFinances = true;
        } else {
            $canSeeFinances = false;
        }

        $projectData = $this->fillProjectsData();

        $this->view->pageData = array(
            'canSeeFinances'   => $canSeeFinances,
            'projectsList' => $projectData,

        );


    }

    public function indexAction() {

        $user = Zend_Registry::get('user');
        $test = new C3op_Access_UserCanSeeFinances($user);
        if ($test->can()) {
            $canSeeFinances = true;
        } else {
            $canSeeFinances = false;

        }


        $projectData = $this->fillProjectsData();

        $this->view->pageData = array(
            'canSeeFinances'   => $canSeeFinances,
            'projectsList' => $projectData,

        );


    }

    private function fillProjectsData() {


        $this->initInstitutionMapper();
        $this->initContactMapper();
        $this->initReceivableMapper();
        $this->initDeliveryMapper();
        $this->initActionMapper();


        $projects = $this->projectMapper->getAllActiveProjects();
        $data = array();




        foreach ($projects as $projectId) {
            $thisProject = $this->projectMapper->findById($projectId);

            $obj = new C3op_Projects_ProjectStatusTypes();
            $projectStatus = $obj->TitleForType($thisProject->getStatus());



            $clientName = $this->view->translate('#(not defined)');
            if ($thisProject->getClient() > 0) {
                $thisClient = $this->institutionMapper->findById($thisProject->getClient());
                $clientName = $thisClient->GetShortName();
            }

            $nextDeliveryObj = $this->deliveryMapper->findNextDeliveryAtProject($projectId);
            $nextDeliveryLabel = $this->view->translate("#(unknown date)");
            $nextDifferenceInDays = "&nbsp;";
            $nextDeliveryValue = "&nbsp;";
            if ($nextDeliveryObj) {

                $validator = new C3op_Util_ValidDate();
                $contractualDeliveryDate = $nextDeliveryObj->getPredictedDate();
                $nextDeliveryDue = false;

                $currencyDisplay = new  C3op_Util_CurrencyDisplay();
                $nextDeliveryValue = $currencyDisplay->FormatCurrency($nextDeliveryObj->GetReceivablePredictedValue());

                if ($validator->isValid($contractualDeliveryDate)) {

                    $dateDiff = new C3op_Util_DatesDifferenceInDays();
                    $now = time(); // or your date as well
                    $nextDifferenceInDays = $dateDiff->differenceInDays(strtotime($contractualDeliveryDate), $now);

                    if ($nextDifferenceInDays < 0) {
                        $nextDeliveryDue = true;
                    }

                    $nextDeliveryLabel = C3op_Util_DateDisplay::FormatDateToShow($nextDeliveryObj->getPredictedDate());
                } else {
                    $nextDeliveryLabel = $this->view->translate("#(undefined date)");
                    $nextDifferenceInDays = "0";
                }





            }



            $projectReceivables = $this->receivableMapper->getAllReceivables($thisProject);
            $receivablesData = array();

            foreach ($projectReceivables as $receivableId) {

                $theReceivable = $this->receivableMapper->findById($receivableId);
                $receivableTitle = $theReceivable->getTitle();

                $contractualDeliveryDate = $theReceivable->getDeliveryDate();
                $validator = new C3op_Util_ValidDate();
                if ($validator->isValid($contractualDeliveryDate)) {
                    $formatedContractualDeliveryDate = C3op_Util_DateDisplay::FormatDateToShow($contractualDeliveryDate);
                } else {
                    $formatedContractualDeliveryDate = $this->view->translate("#(undefined date)");
                }

                $actualDeliveryDate = "";
                $tester = new C3op_Projects_DeliveryMade($theReceivable, $this->deliveryMapper);
                $formatedActualDeliveryDate = $this->view->translate("#(not delivered)");
                if (!$tester->wasDelivered()) {
                    $validator = new C3op_Util_ValidDate();
                    $deliveryDue = true;
                    if ($validator->isValid($contractualDeliveryDate)) {
                        $now = time(); // or your date as well
                        $datediff = strtotime($contractualDeliveryDate) - $now;
                        $differenceInDays = floor($datediff/(60*60*24));
                        if ($differenceInDays >= 0) {
                            $deliveryDue = false;
                        }
                    } else {
                        $differenceInDays = "0";
                    }
                } else {
                    $actualDeliveryDate = $theReceivable->getRealDate();
                    $formatedActualDeliveryDate = C3op_Util_DateDisplay::FormatDateToShow($actualDeliveryDate);
                }


                $currencyDisplay = new  C3op_Util_CurrencyDisplay();

                $predictedValue = $currencyDisplay->FormatCurrency($theReceivable->getPredictedValue());

                $requiredProducts = $this->receivableMapper->getAllProducts($theReceivable);
                $requiredProductsData = array();
                $statusTypes = new C3op_Projects_ActionStatusTypes();

                foreach($requiredProducts as $productId) {

                    $loopProduct = $this->actionMapper->findById($productId);
                    $productData = array();
                    $productData['productName'] = $loopProduct->getTitle();

                    $predictedFinishDate = $loopProduct->getPredictedFinishDate();
                    if ($validator->isValid($predictedFinishDate)) {
                        $formatedPredictedDate = C3op_Util_DateDisplay::FormatDateToShow($predictedFinishDate);
                    } else {
                        $formatedPredictedDate = $this->view->translate("#(undefined date)");
                    }

                    $realFinishDate = "";
                    $formatedRealDate = $this->view->translate("#(not done)");
                    $tester = new C3op_Projects_ActionDone($loopProduct);
                    if (!$tester->isDone()) {
                        $validator = new C3op_Util_ValidDate();
                        $deliveryDue = true;
                        if ($validator->isValid($predictedFinishDate)) {
                            $dateDiff = new C3op_Util_DatesDifferenceInDays();
                            $now = time(); // or your date as well
                            $differenceInDays = $dateDiff->differenceInDays(strtotime($predictedFinishDate), $now);
                            if ($differenceInDays >= 0) {
                                $deliveryDue = false;
                            }
                        } else {
                            $differenceInDays = "0";
                        }
                    } else {
                        $realFinishDate = $loopProduct->getRealFinishDate();
                        $formatedRealDate = C3op_Util_DateDisplay::FormatDateToShow($realFinishDate);
                        $differenceInDays = "0";
                    }

                    $productData['realFinishDate'] = $formatedRealDate;
                    $productData['predictedFinishDate'] = $formatedPredictedDate;
                    $productData['differenceInDays'] = $differenceInDays;
                    $productData['deliveryDue'] = $deliveryDue;





                    if ($loopProduct->getSupervisor()) {
                        $theContact = $this->contactMapper->findById($loopProduct->getSupervisor());
                        $productData['responsibleName'] = $theContact->getName();
                    } else {
                        $productData['responsibleName'] = $this->view->translate("#Not defined");
                    }

                    $productData['status'] = $statusTypes->TitleForType($loopProduct->getStatus());

                    $requiredProductsData[$productId] = $productData;

                }




                $receivableData = array(
                    'contractualDeliveryDate' => $formatedContractualDeliveryDate,
                    'actualDeliveryDate'      => $formatedActualDeliveryDate,
                    'deliveryDue'             => $deliveryDue,
                    'receivableValue'         => $predictedValue,
                    'receivableTitle'         => $receivableTitle,
                    'differenceInDays'        => "$differenceInDays",
                    'productsList'            => $requiredProductsData,

                );
                $receivablesData[$receivableId] = $receivableData;


            }
            $projectData = array(
                'projectName'      => $thisProject->getShortTitle(),
                'status'           => $projectStatus,
                'clientName'       => $clientName,
                'differenceInDays' => $nextDifferenceInDays,
                'deliveryValue'    => $nextDeliveryValue,
                'deliveryDate'     => $nextDeliveryLabel,
                'deliveryDue'      => $nextDeliveryDue,
                'receivablesList'  => $receivablesData,

            );

            $data[$projectId] = $projectData;

        }

        return $data;


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

   private function initInstitutionMapper()
    {
         $this->institutionMapper = new C3op_Register_InstitutionMapper($this->db);
    }

    private function initReceivableMapper()
    {
        if (!isset($this->receivableMapper)) {
            $this->receivableMapper = new C3op_Finances_ReceivableMapper($this->db);
        }
    }

    private function initDeliveryMapper()
    {
        if (!isset($this->deliveryMapper)) {
            $this->deliveryMapper = new C3op_Projects_DeliveryMapper($this->db);
        }
    }



}