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

        $this->view->pageUri = "";
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

        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('list', 'html')
                    ->addActionContext('modify', 'html')
                    ->initContext();

   }

    private function fillAllProjectsAction()
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

        $list = $this->projectMapper->getAllProjects();
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


            }


        $data = $projectsList;

        return $data;


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

        $this->view->pageTitle = $this->view->translate("#Active Projects");
        $this->view->pageUri = "/projects/index/active";
        $this->_helper->layout()->getView()->headTitle($this->view->pageTitle);


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
        $receiptsData = $this->fillReceiptsData();
        $allProjects = $this->fillAllProjectsAction();
        $responsiblesNextActions = $this->fillResponsiblesData();
        $delayedData = $this->fillDelayedData();
        $doneData = $this->fillDoneData();
        $rejectedData = $this->fillRejectedData();

        $this->view->pageData = array(
            'canSeeFinances'   => $canSeeFinances,
            'projectsList'     => $projectData,
            'receiptsList'     => $receiptsData,
            'allProjectsList'  => $allProjects,
            'responsiblesList' => $responsiblesNextActions,
            'delayedList'      => $delayedData,
            'doneList'         => $doneData,
            'rejectedList'     => $rejectedData,

        );

        $this->view->pageTitle = $this->view->translate("#Projects");
        $this->view->pageUri = "/projects";
        $this->_helper->layout()->getView()->headTitle($this->view->pageTitle);


    }

    private function fillProjectsData($all = false) {


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




        foreach ($projects as $projectId) {
            $loopProject = $this->projectMapper->findById($projectId);

            $obj = new C3op_Projects_ProjectStatusTypes();
            $projectStatus = $obj->TitleForType($loopProject->getStatus());

            $clientName = $this->view->translate('#(not defined)');
            if ($loopProject->getClient() > 0) {
                $thisClient = $this->institutionMapper->findById($loopProject->getClient());
                $clientName = $thisClient->GetShortName();
            }

            $projectFinishDate = $loopProject->getFinishDate();
            $labelProjectFinishDate = C3op_Util_DateDisplay::FormatDateToShow($projectFinishDate);

            $validator = new C3op_Util_ValidDate();
            $projectDue = false;
            $projectDifferenceInDays = "";
            if ((!is_null($projectFinishDate)) && ($validator->isValid($projectFinishDate))) {
                $dateDiff = new C3op_Util_DatesDifferenceInDays();
                $now = time();
                $projectDifferenceInDays = $dateDiff->differenceInDays(strtotime($projectFinishDate), $now);
                if ($projectDifferenceInDays < 0) {
                    $projectDue = true;
                }

            }

            $currencyDisplay = new  C3op_Util_CurrencyDisplay();
            $projectValue = $currencyDisplay->FormatCurrency($loopProject->getValue());



            $projectReceivables = $this->receivableMapper->getAllReceivables($loopProject);
            $receivablesData = array();
            $receivableStatus = C3op_Finances_ReceivableStatusConstants::STATUS_NOT_DELIVERED;

            foreach ($projectReceivables as $receivableId) {

                $loopReceivable = $this->receivableMapper->findById($receivableId);
                $receivableTitle = $loopReceivable->getTitle();

                $contractualDeliveryDate = $loopReceivable->getDeliveryDate();
                $validator = new C3op_Util_ValidDate();
                if ($validator->isValid($contractualDeliveryDate)) {
                    $formatedContractualDeliveryDate = C3op_Util_DateDisplay::FormatDateToShow($contractualDeliveryDate);
                } else {
                    $formatedContractualDeliveryDate = $this->view->translate("#(undefined date)");
                }

                $actualDeliveryDate = "";
                $tester = new C3op_Projects_DeliveryMade($loopReceivable, $this->deliveryMapper);
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
                    $actualDeliveryDate = $loopReceivable->getRealDate();
                    $formatedActualDeliveryDate = C3op_Util_DateDisplay::FormatDateToShow($actualDeliveryDate);
                }


                $currencyDisplay = new  C3op_Util_CurrencyDisplay();

                $predictedValue = $currencyDisplay->FormatCurrency($loopReceivable->getPredictedValue());

                $requiredProducts = $this->receivableMapper->getAllProducts($loopReceivable);
                $requiredProductsData = array();
                $statusTypes = new C3op_Projects_ActionStatusTypes();

                $allProductsDelivered = true;
                $noneProductsDelivered = true;
                $someProductDelivered = false;
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

                    $productStatus = $loopProduct->getStatus();
                    if ($productStatus == C3op_Projects_ActionStatusConstants::STATUS_DELIVERED) {
                        $noneProductsDelivered = false;
                        $someProductDelivered = true;
                    } else {
                        $allProductsDelivered = false;
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

                    $dateFinder = new C3op_Finances_ProductDeliveryDate($loopProduct, $this->actionMapper);
                    $rawProductDeliveryDate = $dateFinder->retrieve();
                    $validator = new C3op_Util_ValidDate();
                    if ($validator->isValid($rawProductDeliveryDate)) {
                        $productDeliveryDate = C3op_Util_DateDisplay::FormatDateToShow($rawProductDeliveryDate);
                    } else {
                        $productDeliveryDate = $this->view->translate("#(not defined)");
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

                if ($noneProductsDelivered) {
                    $receivableStatus = C3op_Finances_ReceivableStatusConstants::STATUS_NOT_DELIVERED;
                } else if ($allProductsDelivered) {
                    $receivableStatus = C3op_Finances_ReceivableStatusConstants::STATUS_DELIVERED;
                }  else if ($someProductDelivered) {
                    $receivableStatus = C3op_Finances_ReceivableStatusConstants::STATUS_PARTIAL;
                }
                $obj = new C3op_Finances_ReceivableStatusTypes();
                $receivableStatus = $this->view->translate($obj->TitleForType($receivableStatus));



                $receivableData = array(
                    'contractualDeliveryDate' => $formatedContractualDeliveryDate,
                    'actualDeliveryDate'      => $formatedActualDeliveryDate,
                    'deliveryDue'             => $deliveryDue,
                    'receivableValue'         => $predictedValue,
                    'receivableTitle'         => $receivableTitle,
                    'differenceInDays'        => "$differenceInDays",
                    'productsList'            => $requiredProductsData,
                    'receivableStatus'        => $receivableStatus,
                );
                $receivablesData[$receivableId] = $receivableData;


            }
            $projectData = array(
                'projectName'      => $loopProject->getShortTitle(),
                'status'           => $projectStatus,
                'clientName'       => $clientName,
                'differenceInDays' => $projectDifferenceInDays,
                'projectValue'     => $projectValue,
                'finishDate'       => $labelProjectFinishDate,
                'deliveryDue'      => $projectDue,
                'receivablesList'  => $receivablesData,

            );

            $data[$projectId] = $projectData;

        }

        return $data;


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

//        $project = $this->initProjectWithCheckedId($this->projectMapper);
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

        $actionsEngaged = $this->responsibleMapper->getAllActionsEngagingInActiveProjects($contactId, $institutionId);

        $engagedActions = array();
        $alreadyPayedValue = 0;
        $totalProvidedValue = 0;
        $totalContractedValue = 0;
        $currencyDisplay = new  C3op_Util_CurrencyDisplay();

        foreach ($actionsEngaged as $id => $data) {
            $action = $this->actionMapper->findById($id);
            $responsible = $this->responsibleMapper->findById($data['responsible']);
            $loopProject = $this->projectMapper->findById($action->getProject());

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
                'projectId'            => $action->getProject(),
                'projectTitle'         => $loopProject->getShortTitle(),
                'responsibleId'        => $responsible->getId(),
                'title'                => $action->getTitle(),
                'position'             => "??? out of use ???",
//                'payedValue'          => $actionPayedValue,
//                'totalValue'          => $actionTotalValue,
                'payedValue'           => 'N/D',
                'totalValue'           => 'N/D',
                'actionStatus'         => $this->view->translate($actionStatusLabel),
                'responsibleStatus'    => $this->view->translate($responsibleStatusLabel),
                'canContractFlag'      => $canContract,
                'canProvideOutlay'     => $canProvideOutlay,
                'canNotifyOutlay'      => $canNotifyOutlay,
                'canEditResource'      => $canEditResource,
                'canRemoveResponsible' => $canRemoveResponsible,
                'outlayId'             => $outlayId,
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
            'type'            => $engagedType,
            'contactName'     => $contactName,
            'contactId'       => $contactId,
            'institutionName' => $institutionName,
            'institutionId'   => $institutionId,
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
            $this->view->pageTitle = sprintf($this->view->translate("#%s's participation in active projects"), $contactName);
            $this->view->pageUri = "/projects/index/engagement/?contact=$contactId";
        } else {
            $this->view->pageTitle = sprintf($this->view->translate("#%s's participation in active projects"), $institutionName);
            $this->view->pageUri = "/projects/index/engagement/?institution=$institutionId";
        }
        $this->_helper->layout()->getView()->headTitle($this->view->pageTitle);

    }



    private function fillReceiptsData()
    {
        $this->initActionMapper();

        $receivedActions = $this->actionMapper->getAllReceivedActions();
        $data = array();

        foreach ($receivedActions as $actionId) {
            $loopAction = $this->actionMapper->findById($actionId);
            $actionTitle = $loopAction->GetTitle();
            $loopProject = $this->projectMapper->findById($loopAction->getProject());

            $responsible = new C3op_Projects_ActionResponsible($loopAction, $this->actionMapper, $this->db);
            if ($responsible->doesItHaveAResponsible()) {
                $responsibleData = $responsible->fetch();
            } else {
                $responsibleData = array(
                    'contactId' => '0',
                    'contactName' => _('#(unassigned)'),
                    );

            }

            $validator = new C3op_Util_ValidDate();
            $rawReceiptDate = $loopAction->getReceiptDate($this->actionMapper);
            if ($validator->isValid($rawReceiptDate)) {
                $receiptDate = C3op_Util_DateDisplay::FormatDateToShow($rawReceiptDate);
            } else {
                $receiptDate = "#(not received)";
            }


            if ($validator->isValid($loopAction->getPredictedFinishDate())) {
                $predictedFinishDate = C3op_Util_DateDisplay::FormatDateToShow($loopAction->getPredictedFinishDate());
            } else {
                $predictedFinishDate = "#(not received)";
            }


            $finder = new C3op_Projects_ActionRelatedProduct($loopAction, $this->actionMapper);
            $productData = $finder->fetchProductData();
            foreach ($productData as $k => $val) {
                $productData[$k] = $val;
            }





            $data[$actionId] = array(
                'projectId'           => $loopProject->getId(),
                'projectTitle'        => $loopProject->getShortTitle(),
                'actionId'            => $actionId,
                'actionTitle'         => $actionTitle,
                'contactId'           => $responsibleData['contactId'],
                'contactName'         => $responsibleData['contactName'],
                'receiptDate'         => $receiptDate,
                'predictedFinishDate' => $predictedFinishDate,
                'deliveryDate'        => $productData['productDeliveryDate'],
                'relatedProductTitle' => $productData['relatedProductTitle'],
                'relatedProductId'    => $productData['relatedProductId'],
            );
        }
        return $data;

    }

    private function fillDelayedData()
    {
        $this->initActionMapper();

        $delayedActions = $this->actionMapper->getAllDelayedActions();
        $data = array();

        foreach ($delayedActions as $actionId) {
            $loopAction = $this->actionMapper->findById($actionId);
            $actionTitle = $loopAction->GetTitle();
            $loopProject = $this->projectMapper->findById($loopAction->getProject());

            $responsibleFinder = new C3op_Projects_ActionResponsible($loopAction, $this->actionMapper, $this->db);
            $institutionId = 0;
            $contactId = 0;
            $personal = false;
            if ($responsibleFinder->doesItHaveAResponsible()) {
                $responsibleData = $responsibleFinder->fetch();
                $loopResponsible = $this->responsibleMapper->findById($responsibleData['responsibleId']);

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


            } else {
                $responsibleData = array(
                    'contactId' => '0',
                    'responsibleLabel' => _('#(unassigned)'),
                    'personal'    => false,
                    );

            }





            $validator = new C3op_Util_ValidDate();
            $rawReceiptDate = $loopAction->getReceiptDate($this->actionMapper);
            if ($validator->isValid($rawReceiptDate)) {
                $receiptDate = C3op_Util_DateDisplay::FormatDateToShow($rawReceiptDate);
            } else {
                $receiptDate = "#(not received)";
            }


            if ($validator->isValid($loopAction->getPredictedFinishDate())) {
                $predictedFinishDate = C3op_Util_DateDisplay::FormatDateToShow($loopAction->getPredictedFinishDate());
            } else {
                $predictedFinishDate = "#(not defined)";
            }

            if ($validator->isValid($loopAction->getBaselineFinishDate())) {
                $dateDiff = new C3op_Util_DatesDifferenceInDays();
                $now = time();
                $daysOfDelay = $dateDiff->differenceInDays(strtotime($loopAction->getBaselineFinishDate()), $now);
                $baselineFinishDate = C3op_Util_DateDisplay::FormatDateToShow($loopAction->getBaselineFinishDate());
            } else {
                $baselineFinishDate = $this->view->translate("#(not defined)");
                $daysOfDelay = $this->view->translate("#??");
            }


            $finder = new C3op_Projects_ActionRelatedProduct($loopAction, $this->actionMapper);
            $productObj = $finder->retrieve();
            $productData = $finder->fetchProductData();
            foreach ($productData as $k => $val) {
                $productData[$k] = $val;
            }

            $finder = new C3op_Finances_ProductReceivableValue($productObj, $this->actionMapper);
            $receivableRawValue = $finder->retrieve($this->receivableMapper);
            $currencyDisplay = new  C3op_Util_CurrencyDisplay();
            $receivableValue = $currencyDisplay->FormatCurrency($receivableRawValue);

            $actionValueObj = new C3op_Projects_ActionCost($loopAction,$this->actionMapper);
            $currencyDisplay = new  C3op_Util_CurrencyDisplay();
            $actionsBelow = new C3op_Projects_ActionsBelow($loopAction,$this->actionMapper);
            $rawValue = $actionValueObj->totalActionTreeCost($actionsBelow
                                                                , new C3op_Resources_MaterialSupplyMapper
                                                                , new C3op_Resources_ResponsibleMapper
                                                            );
            $actionValue = $currencyDisplay->FormatCurrency($rawValue);




            $data[$actionId] = array(
                'projectId'           => $loopProject->getId(),
                'projectTitle'        => $loopProject->getShortTitle(),
                'actionId'            => $actionId,
                'actionTitle'         => $actionTitle,
                'contactId'           => $contactId,
                'receivableValue'     => $receivableValue,
                'institutionId'       => $institutionId,
                'personal'            => $personal,
                'name'                => $responsibleData['responsibleLabel'],
                'value'               => $actionValue,
                'daysOfDelay'         => $daysOfDelay,
                'baselineFinishDate'  => $baselineFinishDate,
                'predictedFinishDate' => $predictedFinishDate,
                'deliveryDate'        => $productData['productDeliveryDate'],
                'relatedProductTitle' => $productData['relatedProductTitle'],
                'relatedProductId'    => $productData['relatedProductId'],
            );
        }
        return $data;

    }


    private function fillDoneData()
    {
        $this->initActionMapper();

        $actions = $this->actionMapper->getAllDoneActions();
        $data = array();

        foreach ($actions as $actionId) {
            $loopAction = $this->actionMapper->findById($actionId);
            $actionTitle = $loopAction->GetTitle();
            $loopProject = $this->projectMapper->findById($loopAction->getProject());

            $responsible = new C3op_Projects_ActionResponsible($loopAction, $this->actionMapper, $this->db);
            if ($responsible->doesItHaveAResponsible()) {
                $responsibleData = $responsible->fetch();
            } else {
                $responsibleData = array(
                    'contactId' => '0',
                    'contactName' => _('#(unassigned)'),
                    'canProvideOutlay' => false,
                    'responsibleId'    =>  '0',
                    );

            }

            $validator = new C3op_Util_ValidDate();
            $rawReceiptDate = $loopAction->getReceiptDate($this->actionMapper);
            if ($validator->isValid($rawReceiptDate)) {
                $receiptDate = C3op_Util_DateDisplay::FormatDateToShow($rawReceiptDate);
            } else {
                $receiptDate = "#(not received)";
            }


            if ($validator->isValid($loopAction->getPredictedFinishDate())) {
                $predictedFinishDate = C3op_Util_DateDisplay::FormatDateToShow($loopAction->getPredictedFinishDate());
            } else {
                $predictedFinishDate = "#(not received)";
            }


            $finder = new C3op_Projects_ActionRelatedProduct($loopAction, $this->actionMapper);
            $productData = $finder->fetchProductData();
            foreach ($productData as $k => $val) {
                $productData[$k] = $val;
            }





            $data[$actionId] = array(
                'projectId'           => $loopProject->getId(),
                'projectTitle'        => $loopProject->getShortTitle(),
                'actionId'            => $actionId,
                'actionTitle'         => $actionTitle,
                'contactId'           => $responsibleData['contactId'],
                'contactName'         => $responsibleData['contactName'],
                'canProvideOutlay'    => $responsibleData['canProvideOutlay'],
                'responsibleId'       => $responsibleData['responsibleId'],
                'receiptDate'         => $receiptDate,
                'predictedFinishDate' => $predictedFinishDate,
                'deliveryDate'        => $productData['productDeliveryDate'],
                'relatedProductTitle' => $productData['relatedProductTitle'],
                'relatedProductId'    => $productData['relatedProductId'],
            );
        }
        return $data;

    }

    private function fillRejectedData()
    {
        $this->initActionMapper();

        $actions = $this->actionMapper->getAllRejectedActions();
        $data = array();

        foreach ($actions as $actionId) {
            $loopAction = $this->actionMapper->findById($actionId);
            $actionTitle = $loopAction->GetTitle();
            $loopProject = $this->projectMapper->findById($loopAction->getProject());

            $responsible = new C3op_Projects_ActionResponsible($loopAction, $this->actionMapper, $this->db);
            if ($responsible->doesItHaveAResponsible()) {
                $responsibleData = $responsible->fetch();
            } else {
                $responsibleData = array(
                    'contactId'        => '0',
                    'contactName'      => _('#(unassigned)'),
                    'canProvideOutlay' => false,
                    'responsibleId'    =>  '0',
                    );

            }

            $validator = new C3op_Util_ValidDate();
            $rawReceiptDate = $loopAction->getReceiptDate($this->actionMapper);
            if ($validator->isValid($rawReceiptDate)) {
                $receiptDate = C3op_Util_DateDisplay::FormatDateToShow($rawReceiptDate);
            } else {
                $receiptDate = "#(not received)";
            }


            if ($validator->isValid($loopAction->getPredictedFinishDate())) {
                $predictedFinishDate = C3op_Util_DateDisplay::FormatDateToShow($loopAction->getPredictedFinishDate());
            } else {
                $predictedFinishDate = "#(not received)";
            }


            $finder = new C3op_Projects_ActionRelatedProduct($loopAction, $this->actionMapper);
            $productData = $finder->fetchProductData();
            foreach ($productData as $k => $val) {
                $productData[$k] = $val;
            }





            $data[$actionId] = array(
                'projectId'           => $loopProject->getId(),
                'projectTitle'        => $loopProject->getShortTitle(),
                'actionId'            => $actionId,
                'actionTitle'         => $actionTitle,
                'contactId'           => $responsibleData['contactId'],
                'contactName'         => $responsibleData['contactName'],
                'canProvideOutlay'    => $responsibleData['canProvideOutlay'],
                'responsibleId'       => $responsibleData['responsibleId'],
                'receiptDate'         => $receiptDate,
                'predictedFinishDate' => $predictedFinishDate,
                'deliveryDate'        => $productData['productDeliveryDate'],
                'relatedProductTitle' => $productData['relatedProductTitle'],
                'relatedProductId'    => $productData['relatedProductId'],
            );
        }
        return $data;

    }

    private function fillResponsiblesData()
    {
        $this->initResponsibleMapper();

        $responsiblesList = $this->responsibleMapper->getAllResponsiblesWithNextActionsAtActiveProjects();
        $data = array();
        foreach ($responsiblesList as $id) {
            $loopResponsible = $this->responsibleMapper->findById($id);
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


            $projectsIds = $this->responsibleMapper->getAllActiveProjectsEngaging($contactId, $institutionId);

            $projectsData = array();
            foreach($projectsIds as $projectId => $responsibleId) {
                $loopProject = $this->projectMapper->findById($projectId);
                $projectsData[$projectId] = array(
                    'projectTitle' => $loopProject->getShortTitle(),
                );

            }

            $actionsIds = $this->responsibleMapper->getNextActionsEngagingInActiveProjects($contactId, $institutionId, 7);

            $actionsData = array();
            foreach($actionsIds as $actionId => $responsibleId) {
                $loopAction = $this->actionMapper->findById($actionId);
                $actionsData[$actionId] = array(
                    'actionTitle' => $loopAction->getTitle(),
                );

            }

            $data[$id] = array(
                'contactId' => $contactId,
                'institutionId' => $institutionId,
                'name' => $responsibleLabel,
                'personal' => $personal,
                'projectsData' => $projectsData,
                'actionsData' => $actionsData,
            );
        }

        return $data;

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

    private function initActionMapper()
    {
        if (!isset($this->actionMapper)) {
            $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
        }
    }

    private function initContactWithCheckedContactId(C3op_Register_ContactMapper $mapper)
    {
        return $mapper->findById($this->checkContactFromGet());
    }

   private function initContactMapper()
    {
         $this->contactMapper = new C3op_Register_ContactMapper($this->db);
    }

    private function initOutlayMapper()
    {
        if (!isset($this->outlayMapper)) {
            $this->outlayMapper = new C3op_Finances_OutlayMapper($this->db);
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

    private function initInstitutionWithCheckedInstitutionId(C3op_Register_InstitutionMapper $mapper)
    {
        return $mapper->findById($this->checkInstitutionFromGet());
    }



}