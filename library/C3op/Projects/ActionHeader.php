<?php
class C3op_Projects_ActionHeader {

    private $db;
    private $data;
    private $action;
    private $mapper;



    public function __construct($db, C3op_Projects_Action $action, C3op_Projects_ActionMapper $mapper)
    {
        $this->data = array();

        $this->db = $db;
        $this->action = $action;
        $this->mapper = $mapper;

    }

    public function fetch($financesData=false)
    {
        $this->fillMainData();
        $this->fillDatesData($this->action, $this->data);
        $this->fillSupervisorData();
        $this->fillProjectData();
        $this->fillRelatedActionsData();
        $this->fillResponsibleData();
        $this->fillActionsFlowData();
        if ($financesData) {
            $this->fillValueData();
        }
        $this->fillBudgetData();
        $this->fillProductRelatedData();

        return $this->data;

    }

    private function fillActionsFlowData()
    {
        $removal = new C3op_Projects_ActionRemoval($this->action, $this->mapper);

        if ($removal->canBeRemoved()) {
            $canRemoveAction = true;
        } else {
            $canRemoveAction = false;
        }

        $user = Zend_Registry::get('user');
        $acl = Zend_Registry::get('acl');

        $receiptToAcceptOrReject = false;
        if ($this->action->GetStatus() == C3op_Projects_ActionStatusConstants::STATUS_RECEIVED) {
            $tester = new C3op_Access_PrivilegeTester($user, $acl, "projects", "action", "accept-receipt");
            if ($tester->allow()) {
                $receiptToAcceptOrReject = true;
            }
        }

        $waitingToReceipt = false;
        if ($this->action->hasBegun()) {
            $tester = new C3op_Access_PrivilegeTester($user, $acl, "projects", "action", "acknowledge-receipt");
            if ($tester->allow()) {
                if ($this->action->waitingToReceipt()) {
                    $waitingToReceipt = true;
                }
            }

        }


        $this->data['canRemoveAction'] = $canRemoveAction;
        $this->data['receiptToAcceptOrReject'] = $receiptToAcceptOrReject;
        $this->data['waitingToReceipt'] = $waitingToReceipt;


    }

    private function fillBudgetData()
    {

        $user = Zend_Registry::get('user');
        $acl = Zend_Registry::get('acl');

        $canEditBudget = false;
        $tester = new C3op_Access_PrivilegeTester($user, $acl, "projects", "action", "budget-cretae");
        if ($tester->allow()) {
            $canEditBudget = true;
        }
        $this->data['canEditBudget'] = $canEditBudget;


        $budgetForecast = $this->action->getBudgetForecast();
        if ($budgetForecast > 0) {
            $this->data['hasBudget'] = true;
            $this->data['budgetForecast'] = $budgetForecast;
        } else {
            $this->data['hasBudget'] = false;
            $this->data['budgetForecast'] = 0;

        }

    }

    private function fillDatesData(C3op_Projects_Action $action, &$data)
    {
        $validator = new C3op_Util_ValidDate();

        if ($validator->isValid($action->getPredictedBeginDate())) {
            $predictedBeginDate = C3op_Util_DateDisplay::FormatDateToShow($action->getPredictedBeginDate());
        } else {
            $predictedBeginDate = "#(undefined)";
        }

        if ($validator->isValid($action->getPredictedFinishDate())) {
            $predictedFinishDate = C3op_Util_DateDisplay::FormatDateToShow($action->getPredictedFinishDate());
        } else {
            $predictedFinishDate = "#(undefined)";
        }

        if ($action->hasBegun()) {
            $realBeginDate = C3op_Util_DateDisplay::FormatDateToShow($action->getRealBeginDate());

            if (is_null($realBeginDate)) {
                $action->setRealBeginDate($action->getPredictedBeginDate());
                $this->mapper->update($action);
                $realBeginDate = C3op_Util_DateDisplay::FormatDateToShow($action->getRealBeginDate());
            }
        } else {
            $realBeginDate = _("#(not started)");
        }


        $realFinishDate = C3op_Util_DateDisplay::FormatDateToShow($action->getRealFinishDate());

        if ($validator->isValid($action->getRealFinishDate()) && (!is_null($realFinishDate))) {
            $realFinishDate = C3op_Util_DateDisplay::FormatDateToShow($action->getRealFinishDate());
        } else {
            $realFinishDate = "#(not finished)";
        }


        if ($validator->isValid($action->getReceiptDate($this->mapper))) {
            $receiptDate = C3op_Util_DateDisplay::FormatDateToShow($action->getReceiptDate($this->mapper));
        } else {
            $receiptDate = "#(not received)";
        }




        $data['predictedBeginDate'] = $predictedBeginDate;
        $data['predictedFinishDate'] = $predictedFinishDate;
        $data['realBeginDate'] = $realBeginDate;
        $data['realFinishDate'] = $realFinishDate;
        $data['receiptDate'] = $receiptDate;

    }

    private function fillMainData()
    {

        $this->data['id'] = $this->action->getId();
        $this->data['title'] = $this->action->getTitle();
        $statusTypes = new C3op_Projects_ActionStatusTypes();
        $this->data['status'] =  $statusTypes->TitleForType($this->action->getStatus());
        $this->data['description'] = nl2br($this->action->getDescription());

        if ($this->action->getMilestone()) {
            $this->data['isAMilestone'] = true;
        } else {
            $this->data['isAMilestone'] = false;
        }




    }

    private function fillProductRelatedData()
    {
        $finder = new C3op_Projects_ActionRelatedProduct($this->action, $this->mapper);
        $productRelated = $finder->retrieve();
        $relatedProductId = $productRelated->getId();
        $relatedProductTitle = $productRelated->getTitle();
        $dateFinder = new C3op_Finances_ProductDeliveryDate($productRelated, $this->mapper);
        $productDeliveryDate = $dateFinder->retrieve();
        $validator = new C3op_Util_ValidDate();
        if ($validator->isValid($productDeliveryDate)) {
            $productDeliveryDate = C3op_Util_DateDisplay::FormatDateToShow($productDeliveryDate);
        } else {
            $productDeliveryDate = "#(not defined)";
        }


//die("{$relatedProductId} == {$this->action->getId()}");
        if ($productRelated->getId() == $this->action->getId()) {
            $this->data['notAProduct'] = false;
            $this->data['relatedProductTitle'] = "#(no product)";
            $this->data['productDeliveryDate'] = "#(no product to deliver)";
            $this->data['relatedProductId'] = "0";

        } else {
            $this->data['notAProduct'] = true;
            $this->data['relatedProductTitle'] = $relatedProductTitle;
            $this->data['productDeliveryDate'] = $productDeliveryDate;
            $this->data['relatedProductId'] = $productRelated->getId();

        }




    }

    private function fillProjectData()
    {
        $this->initProjectMapper();

        $project = $this->projectMapper->findById($this->action->getProject());


        $this->data['projectId'] = $project->getId();
        $this->data['projectTitle'] = $project->getShortTitle();



    }

    private function fillRelatedActionsData()
    {

        $this->fillSubordinatedActionsData();

        if ($this->action->getSubordinatedTo() > 0) {
            $parentAction = $this->mapper->findById($this->action->getSubordinatedTo());
            $parentActionTitle = $parentAction->getTitle();
            $parentActionId = $parentAction->getId();
        } else {
            $parentActionTitle = "";
            $parentActionId = 0;
        }

        $this->data['parentActionId'] = $parentActionId;
        $this->data['parentActionTitle'] = $parentActionTitle;

    }

    private function fillResponsibleData()
    {
        $responsible = new C3op_Projects_ActionResponsible($this->action, $this->mapper, $this->db);

        if ($responsible->doesItHasAResponsible()) {
            $data = $responsible->fetch();
            $this->data['hasResponsible'] = true;
            $this->data['responsibleType'] = $data['responsibleType'];
            $this->data['responsibleId'] = $data['responsibleId'];
            $this->data['contactId'] = $data['contactId'];
            $this->data['contactName'] = $data['contactName'];
            $this->data['statusLabel'] = $data['statusLabel'];
            $this->data['responsibleLabel'] = $data['responsibleLabel'];
        } else {
            $this->data['hasResponsible'] = false;
            $this->data['responsibleId'] = 0;
            $this->data['responsibleType'] = 0;
            $this->data['contactId'] = 0;
            $this->data['contactName'] = _('#(unassigned)');
            $this->data['statusLabel'] = _('#(unknown)');

        }

    }

   private function fillSubActionsTreeData($tree)
    {
       // actionInfo
       //   title
       //   subordinatedTo
       //   contactName
       //   predictedBeginDate
       //   realBeginDate
       //   predictedFinishDate
       //   realFinishDate
       //

        foreach ($tree as $id => $subTree) {
            $loopAction = $this->mapper->findById($id);
            $data = array();
            $data['title'] = $loopAction->getTitle();
            $data['subordinatedTo'] = $loopAction->getSubordinatedTo();

            $responsible = new C3op_Projects_ActionResponsible($loopAction, $this->mapper, $this->db);

            if ($responsible->doesItHasAResponsible()) {
                $responsibleData = $responsible->fetch();
                $data['hasResponsible'] = true;
                $data['contactId'] = $responsibleData['contactId'];
                $data['institutionId'] = $responsibleData['institutionId'];
                $data['responsibleLabel'] = $responsibleData['responsibleLabel'];
                $data['statusLabel'] = $responsibleData['statusLabel'];
            } else {
                $data['hasResponsible'] = false;
                $data['contactId'] = 0;
                $data['contactName'] = _('#(unassigned)');
                $data['statusLabel'] = _('#(unknown)');

            }



//            if ($loopAction->getSupervisor()) {
//                $theContact = $this->contactMapper->findById($loopAction->getSupervisor());
//                $data['contactName'] = $theContact->getName();
//            } else {
//                $data['contactName'] = "#Not defined";
//            }

            $this->fillDatesData($loopAction, $data);

//            $data['predictedBeginDate'] = C3op_Util_DateDisplay::FormatDateToShow($loopAction->getPredictedBeginDate());
//            $data['realBeginDate'] = C3op_Util_DateDisplay::FormatDateToShow($loopAction->getRealBeginDate());
//            $data['predictedFinishDate'] = C3op_Util_DateDisplay::FormatDateToShow($loopAction->getPredictedFinishDate());
//            $data['realFinishDate'] = C3op_Util_DateDisplay::FormatDateToShow($loopAction->getRealFinishDate());

            $this->data['subActionsTree'][$id] = $data;
            $this->fillSubActionsTreeData($subTree);
        }
    }


    private function fillSubordinatedActionsData()
    {
        $this->data['subActionsTree'] = array();
        $obj = new C3op_Projects_ActionTree();
        $tree = $obj->retrieveTree($this->action, $this->mapper);
        $this->fillSubActionsTreeData($tree);

    }

    private function fillSupervisorData()
    {

        $this->initContactMapper();
        $supervisorId = $this->action->getSupervisor();
        if ($supervisorId > 0) {
            $supervisorContact = $this->contactMapper->findById($supervisorId);
            $supervisorName = $supervisorContact->getName();
        } else {
            $supervisorName = "#Not defined";
        }


        $this->data['supervisorId'] = $supervisorId;
        $this->data['supervisorName'] = $supervisorName;

    }

    private function fillValueData()
    {
        $actionValue = new C3op_Projects_ActionValue($this->action,$this->mapper);
        $currencyDisplay = new  C3op_Util_CurrencyDisplay();
        $this->data['totalContractedValue'] = $currencyDisplay->FormatCurrency($actionValue->totalValue());
        $this->data['hasBudget'] = false;
    }


    private function initContactMapper()
    {
        if (!isset($this->contactMapper)) {
             $this->contactMapper = new C3op_Register_ContactMapper($this->db);
        }
    }

    private function initProjectMapper()
    {
        if (!isset($this->projectMapper)) {
             $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
        }
    }



}
