<?php
class C3op_Projects_ActionHeader {

    private $db;
    private $data;
    private $action;
    private $mapper;



    public function __construct($db, C3op_Projects_Action $action, C3op_Projects_ActionMapper $mapper)
    {
        $this->data = array(
//            'notAProduct'             => "1",
//            'relatedProductTitle'     => "um produto",
//            'relatedProductId'        => "2",
//            'productDeliveryDate'     => "03-5-2010",

        );

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
        $this->fillActionsFlowData();
        if ($financesData) {
            $this->fillValueData();
        }
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

        $receiptToAcceptOrReject = false;
        if ($this->action->GetStatus() == C3op_Projects_ActionStatusConstants::STATUS_RECEIVED) {
            $tester = new C3op_Access_PrivilegeTester("projects", "action", "accept-receipt");
            if ($tester->allow()) {
                $receiptToAcceptOrReject = true;
            }
        }

        $waitingToReceipt = false;
        if ($this->action->hasBegun()) {
            $tester = new C3op_Access_PrivilegeTester("projects", "action", "acknowledge-receipt");
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

   private function fillSubActionsTreeData($tree)
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

        foreach ($tree as $id => $subTree) {
            $loopAction = $this->mapper->findById($id);
            $data = array();
            $data['title'] = $loopAction->getTitle();
            $data['subordinatedTo'] = $loopAction->getSubordinatedTo();

            if ($loopAction->getSupervisor()) {
                $theContact = $this->contactMapper->findById($loopAction->getSupervisor());
                $data['responsibleName'] = $theContact->getName();
            } else {
                $data['responsibleName'] = "#Not defined";
            }

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

    private function fillValueData()
    {
        $actionValue = new C3op_Projects_ActionValue($this->action,$this->mapper);
        $currencyDisplay = new  C3op_Util_CurrencyDisplay();
        $this->data['totalContractedValue'] = $currencyDisplay->FormatCurrency($actionValue->totalValue());
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