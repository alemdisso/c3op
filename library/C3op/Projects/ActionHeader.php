<?php
class C3op_Projects_ActionHeader {

    private $db;
    private $data;
    private $action;
    private $mapper;



    public function __construct($db, C3op_Projects_Action $action, C3op_Projects_ActionMapper $mapper)
    {
        $this->data = array(
            'notAProduct'             => "1",
            'relatedProductTitle'     => "um produto",
            'relatedProductId'        => "2",
            'productDeliveryDate'     => "03-5-2010",

        );

        $this->db = $db;
        $this->action = $action;
        $this->mapper = $mapper;

    }

    public function fetch($financesData=false)
    {
        $this->fillMainData();
        $this->fillDatesData();
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
    private function fillDatesData()
    {
        $validator = new C3op_Util_ValidDate();

        if ($validator->isValid($this->action->getPredictedBeginDate())) {
            $predictedBeginDate = C3op_Util_DateDisplay::FormatDateToShow($this->action->getPredictedBeginDate());
        } else {
            $predictedBeginDate = "#(undefined)";
        }

        if ($validator->isValid($this->action->getPredictedFinishDate())) {
            $predictedFinishDate = C3op_Util_DateDisplay::FormatDateToShow($this->action->getPredictedFinishDate());
        } else {
            $predictedFinishDate = "#(undefined)";
        }

        if ($this->action->hasBegun()) {
            $realBeginDate = C3op_Util_DateDisplay::FormatDateToShow($this->action->getRealBeginDate());

            if (is_null($realBeginDate)) {
                $this->action->setRealBeginDate($this->action->getPredictedBeginDate());
                $this->mapper->update($this->action);
                $realBeginDate = C3op_Util_DateDisplay::FormatDateToShow($this->action->getRealBeginDate());
            }
        } else {
            $realBeginDate = "#(not started)";
        }


        $realFinishDate = C3op_Util_DateDisplay::FormatDateToShow($this->action->getRealFinishDate());
        if ($validator->isValid($this->action->getRealFinishDate())) {
            $realFinishDate = C3op_Util_DateDisplay::FormatDateToShow($this->action->getRealFinishDate());
        } else {
            $realFinishDate = "#(not finished)";
        }


        if ($validator->isValid($this->action->getReceiptDate($this->mapper))) {
            $receiptDate = C3op_Util_DateDisplay::FormatDateToShow($this->action->getReceiptDate($this->mapper));
        } else {
            $receiptDate = "#(not received)";
        }




        $this->data['predictedBeginDate'] = $predictedBeginDate;
        $this->data['predictedFinishDate'] = $predictedFinishDate;
        $this->data['realBeginDate'] = $realBeginDate;
        $this->data['realFinishDate'] = $realFinishDate;
        $this->data['receiptDate'] = $receiptDate;

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


        $notAProduct = true;
        if ($productRelated->getId() == $this->action->getId()) {
            $this->data['notAProduct'] = false;
            $this->data['relatedProductTitle'] = $relatedProductTitle;
            $this->data['productDeliveryDate'] = $productDeliveryDate;
            $this->data['relatedProductId'] = $productRelated->getId();

        } else {
            $this->data['notAProduct'] = true;
            $this->data['relatedProductTitle'] = "#(no product)";
            $this->data['productDeliveryDate'] = "#(no product to deliver)";
            $this->data['relatedProductId'] = "0";

        }




    }


    private function fillSubordinatedActionsData()
    {

        $this->initContactMapper();
        $statusTypes = new C3op_Projects_ActionStatusTypes();
        $subordinatedActionsList = $this->mapper->getActionsSubordinatedTo($this->action);
        $subordinatedActionsData = array();

        foreach($subordinatedActionsList as $loopActionId) {

            $loopAction = $this->mapper->findById($loopActionId);
            $data = array();
            $data['title'] = $loopAction->getTitle();

            if ($loopAction->getSupervisor()) {
                $theContact = $this->contactMapper->findById($loopAction->getSupervisor());
                $data['supervisorName'] = $theContact->getName();
            } else {
                $data['supervisorName'] = "#Not defined";
            }

            $data['status'] = $statusTypes->TitleForType($loopAction->getStatus());

            $removal = new C3op_Projects_ActionRemoval($loopAction, $this->mapper);

            $data['canRemoveAction'] = $removal->canBeRemoved();
            $subordinatedActionsData[$loopActionId] = $data;

        }

        $this->data['subordinatedActions'] = $subordinatedActionsData;


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
