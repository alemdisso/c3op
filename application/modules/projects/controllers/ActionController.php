<?php

class Projects_ActionController extends Zend_Controller_Action
{
    private $actionMapper;
    private $teamMemberMapper;
    private $projectMapper;
    private $receivableMapper;
    private $contactMapper;
    private $db;
    private $treeData;

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
    }

    public function createAction()
    {
        // cria form
        $form = new C3op_Form_ActionCreate;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully created.'));
                $this->_redirect('/projects/action/success/?id=' . $id);
            } else {
                //form error: populate and go back
                $form->populate($postData);
                $this->view->form = $form;
            }
        } else {
            $data = $this->_request->getParams();
            $requirementForReceiving = 0;
            $subordinatedTo = 0;
            if (isset($data['subordinatedTo'])) {
                $subordinatedTo = $data['subordinatedTo'];
                $this->initActionMapper();
                $parentAction = $this->actionMapper->findById($subordinatedTo);
                $projectId = $parentAction->GetProject();
            } elseif (isset($data['requirementForReceiving'])) {
                $requirementForReceiving = $data['requirementForReceiving'];
                $this->initReceivableMapper();
                $parentReceivable = $this->receivableMapper->findById($requirementForReceiving);
                $projectId = $parentReceivable->GetProject();
            } else {
                $projectId = $data['project'];
            }

            $this->populateProjectFields($projectId, $form);
            $this->populateResponsibleField($form);
            $this->populateRequirementForReceivingField($projectId, $form, $requirementForReceiving);
            $this->populateSubordinatedActionsField($projectId, $form, 0, $subordinatedTo);
            $pageData = array('id' => $projectId);
            $this->view->pageData = $pageData;
        }
    }

    public function editAction()
    {
        $form = new C3op_Form_ActionEdit;
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/projects/action/success/?id=' . $id);
            } else {
                //form error: populate and go back
                $form->populate($postData);
                $this->view->form = $form;
            }
        } else {
            $data = $this->_request->getParams();
            $filters = array(
                'id' => new Zend_Filter_Alnum(),
            );
            $validators = array(
                'id' => new C3op_Util_ValidId(),
            );
            $input = new Zend_Filter_Input($filters, $validators, $data);
            if ($input->isValid()) {
                $id = $input->id;

                $this->initActionMapper();
                $inputAction = $this->actionMapper->findById($id);
                $element = $form->getElement('title');
                $element->setValue($inputAction->getTitle());

                $element = $form->getElement('id');
                $element->setValue($id);

                $element = $form->getElement('milestone');
                $element->setValue($inputAction->getMilestone());

                $projectId = $this->populateProjectFields($inputAction->getProject(), $form);

                $element = $form->getElement('description');
                $element->setValue($inputAction->getDescription());

                $this->setDateValueToFormField($form, 'predictedBeginDate', $inputAction->getPredictedBeginDate());
                $this->setDateValueToFormField($form, 'predictedFinishDate', $inputAction->getPredictedFinishDate());

                $user = Zend_Registry::get('user');
                $role = $user->GetRole();
                if ($role == C3op_Access_RolesConstants::ROLE_SYSADMIN) {
                    $element = $form->getElement('status');
                    $element->setValue($inputAction->getStatus());
                } else {
                    $form->removeElement('status');
                }

                $this->populateResponsibleField($form, $inputAction->getResponsible());
                $this->populateRequirementForReceivingField($projectId, $form, $inputAction->getRequirementForReceiving());
                $this->populateSubordinatedActionsField($projectId, $form, $id);
            }

            $pageData = array('id' => $id);
            $this->view->pageData = $pageData;
        }
    }

    public function detailAction()
    {
        $pageData = array();

        $actionsList = array();
        $this->initActionMapper();
        $this->initProjectMapper();
        $this->initContactMapper();
        $this->initTeamMemberMapper();

        $actionToBeDetailed =  $this->initActionWithCheckedId($this->actionMapper);
        $projectToBeDetailed = $this->projectMapper->findById($actionToBeDetailed->getProject());

        //  actionHeader
        //    id
        //    projectId
        //    projectTitle
        //    actionTitle
        //    status
        //    responsibleId
        //    responsibleName
        //    parentActionId
        //    parentActionTitle
        //    subordinatedTree
        //      * id =>
        //        actionTitle
        //        responsibleName
        //        status
        //    description
        //    predictedBeginDate
        //    predictedFinishDate
        //    milestone
        //    realBeginDate
        //    realFinishDate
        //    receiptDate
        //    realizationDate
        //

        $statusTypes = new C3op_Projects_ActionStatusTypes();
        $status = $statusTypes->TitleForType($actionToBeDetailed->getStatus());
        $responsibleId = $actionToBeDetailed->getResponsible();
        if ($responsibleId > 0) {
            $responsibleContact = $this->contactMapper->findById($responsibleId);
            $responsibleName = $responsibleContact->getName();
        } else {
            $responsibleName = $this->view->translate("#Not defined");
        }

        if ($actionToBeDetailed->getSubordinatedTo() > 0) {
            $parentAction = $this->actionMapper->findById($actionToBeDetailed->getSubordinatedTo());
            $parentActionTitle = $parentAction->getTitle();
            $parentActionId = $parentAction->getId();
        } else {
            $parentActionTitle = "";
            $parentActionId = 0;
        }

        $subordinatedActionsList = $this->actionMapper->getActionsSubordinatedTo($actionToBeDetailed);
        $subordinatedActionsData = array();

        foreach($subordinatedActionsList as $loopActionId) {

            $loopAction = $this->actionMapper->findById($loopActionId);
            $data = array();
            $data['title'] = $loopAction->getTitle();

            if ($loopAction->getResponsible()) {
                $theContact = $this->contactMapper->findById($loopAction->getResponsible());
                $data['responsibleName'] = $theContact->getName();
            } else {
                $data['responsibleName'] = $this->view->translate("#Not defined");
            }

            $data['status'] = $statusTypes->TitleForType($loopAction->getStatus());

            $subordinatedActionsData[$loopActionId] = $data;


        }
        if ($actionToBeDetailed->getMilestone()) {
            $milestone = _("#Is a milestone");
        } else {
            $milestone = _("#Is not a milestone");
        }

        $predictedBeginDate = C3op_Util_DateDisplay::FormatDateToShow($actionToBeDetailed->getPredictedBeginDate());

        $predictedFinishDate = C3op_Util_DateDisplay::FormatDateToShow($actionToBeDetailed->getPredictedFinishDate());
        $realFinishDate = C3op_Util_DateDisplay::FormatDateToShow($actionToBeDetailed->getRealFinishDate());

        $validator = new C3op_Util_ValidDate();
        if ($validator->isValid($actionToBeDetailed->getReceiptDate($this->actionMapper))) {
            $receiptDate = C3op_Util_DateDisplay::FormatDateToShow($actionToBeDetailed->getReceiptDate($this->actionMapper));
        } else {
            $receiptDate = $this->view->translate("#(not received)");
        }

        $unacknowledgedStart = false;
        $waitingToReceipt = false;
        $realBeginDate = $this->view->translate("#(not started)");
        if ($actionToBeDetailed->hasBegun()) {
            $realBeginDate = C3op_Util_DateDisplay::FormatDateToShow($actionToBeDetailed->getRealBeginDate());
            $obj = new C3op_Projects_ActionStartMode($actionToBeDetailed, $this->actionMapper);
            if ($obj->isUnacknowledged()) {
                $unacknowledgedStart = true;
            } else {
                if ($actionToBeDetailed->waitingToReceipt()) {
                    $waitingToReceipt = true;
                }
            }
        }
        $receiptToAcceptOrReject = false;
        if ($actionToBeDetailed->GetStatus() == C3op_Projects_ActionStatusConstants::STATUS_RECEIVED) {
            $receiptToAcceptOrReject = true;
        }

        $actionValue = new C3op_Projects_ActionValue($actionToBeDetailed,$this->actionMapper);
        $currencyDisplay = new  C3op_Util_CurrencyDisplay();
        $totalContractedValue = $currencyDisplay->FormatCurrency($actionValue->totalValue());

        $actionHeader = array(
            'id'                      => $actionToBeDetailed->getId(),
            'projectId'               => $projectToBeDetailed->getId(),
            'projectTitle'            => $projectToBeDetailed->getShortTitle(),
            'title'                   => $actionToBeDetailed->getTitle(),
            'status'                  => $status,
            'responsibleId'           => $responsibleId,
            'responsibleName'         => $responsibleName,
            'parentActionId'          => $parentActionId,
            'parentActionTitle'       => $parentActionTitle,
            'subordinatedActions'     => $subordinatedActionsData,
            'description'             => nl2br($actionToBeDetailed->getDescription()),
            'milestone'               => $milestone,
            'predictedBeginDate'      => $predictedBeginDate,
            'predictedFinishDate'     => $predictedFinishDate,
            'realBeginDate'           => $realBeginDate,
            'realFinishDate'          => $realFinishDate,
            'receiptDate'             => $receiptDate,
            'unacknowledgedStart'     => $unacknowledgedStart,
            'receiptToAcceptOrReject' => $receiptToAcceptOrReject,
            'waitingToReceipt'        => $waitingToReceipt,
            'totalContractedValue'    => $totalContractedValue,
        );


        // teamMemberList
        //   * teamMemberInfo
        //      id
        //      name
        //      description
        //      valor
        //      contractingStatus

        $teamMembersList = $this->getTeamMembersList($actionToBeDetailed);

        $pageData = array(
            'actionHeader' => $actionHeader,
            'teamMembersList' => $teamMembersList,
        );

        $this->view->pageData = $pageData;

    }

    public function successAction()
    {
        $actionId = $this->checkIdFromGet();
        if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();
            $this->getResponse()->setHeader('Refresh', '1; URL=/projects/action/detail/?id=' . $actionId);
        } else {
            $this->_redirect('/projects');
        }
    }

    public function acknowledgeReceiptAction()
    {
        $this->_helper->layout->disableLayout();
        //$this->_helper->viewRenderer->setNoRender(TRUE);

        $this->initActionMapper();
        $actionToBeChanged =  $this->initActionWithCheckedId($this->actionMapper);

        $acknowledgment = new C3op_Projects_ReceiptAcknowledgment();
        $acknowledgment->AcknowledgeReceipt($actionToBeChanged, $this->actionMapper);

        $actionHeader = array(
            'id' => $actionToBeChanged->getId(),
        );

        $this->view->actionHeader = $actionHeader;


    }

   public function acceptReceiptAction()
    {
        $this->_helper->layout->disableLayout();
        //$this->_helper->viewRenderer->setNoRender(TRUE);

        $this->initActionMapper();
        $actionToBeChanged =  $this->initActionWithCheckedId($this->actionMapper);
        $acceptance = new C3op_Projects_ReceiptAcceptance();
        $acceptance->AcceptReceipt($actionToBeChanged, $this->actionMapper);

    }

   public function rejectReceiptAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $this->initActionMapper();
        $actionToBeChanged =  $this->initActionWithCheckedId($this->actionMapper);
        $rejection = new C3op_Projects_ReceiptRejection();
        $rejection->RejectReceipt($actionToBeChanged, $this->actionMapper);

    }

    public function acknowledgeStartAction()
    {
        $this->_helper->layout->disableLayout();
        //$this->_helper->viewRenderer->setNoRender(TRUE);

        $id = $this->checkIdFromGet();
        $this->initActionMapper();
        $actionToBeChanged =  $this->initActionWithCheckedId($this->actionMapper);

        $acknowledgment = new C3op_Projects_ActionAcknowledgeStart($actionToBeChanged);

        $this->view->pageData = array('id' => $id);


    }

    private function initActionWithCheckedId(C3op_Projects_ActionMapper $mapper)
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
        throw new C3op_Projects_ActionException("Invalid Action Id from Get");

    }

    private function populateProjectFields($projectId, C3op_Form_ActionCreate $form)
    {
        $validator = new C3op_Util_ValidId();
        if ($validator->isValid($projectId)) {
            $projectField = $form->getElement('project');
            $projectField->setValue($projectId);
            if (!isset($this->projectMapper)) {
                $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
            }
            $theProject = $this->projectMapper->findById($projectId);
            $this->view->projectTitle = $theProject->GetShortTitle();
            $this->view->linkProjectDetail = "/projects/project/detail/?id=" . $theProject->GetId();

            return $projectId;
        } else throw new C3op_Projects_ActionException(_("#It needs a positive integer project id to populate project fields."));

   }

    private function populateSubordinatedActionsField($projectId, C3op_Form_ActionCreate $form, $actionId = 0, $parentActionId = 0)
    {
        $validator = new C3op_Util_ValidId();
        if ($validator->isValid($projectId)) {
            $subordinatedToField = $form->getElement('subordinatedTo');
            $this->initActionMapper();

            if ($actionId > 0) {
                $actionToBePopulated = $this->actionMapper->findById($actionId);
                $parentActionId = $actionToBePopulated->GetSubordinatedTo();
                $allOtherActionsInProject = $this->actionMapper->getAllOtherActions($actionToBePopulated);

            } else {
                if (!isset($this->projectMapper)) {
                    $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
                }
                $theProject = $this->projectMapper->findById($projectId);
                $allOtherActionsInProject = $this->projectMapper->getAllActions($theProject);
            }

            while (list($key, $actionId) = each($allOtherActionsInProject)) {
                $eachAction = $this->actionMapper->findById($actionId);
                $subordinatedToField->addMultiOption($actionId, $eachAction->GetTitle());
            }

            $subordinatedToField->setValue($parentActionId);

        } else throw new C3op_Projects_ActionException(_("#It needs a positive integer project id to find other actions from same project."));
   }

    private function populateRequirementForReceivingField($projectId, C3op_Form_ActionCreate $form, $setedReceivableId = 0)
    {

        $validator = new C3op_Util_ValidId();
        if ($validator->isValid($projectId)) {
            $requirementForReceivingField = $form->getElement('requirementForReceiving');

            $this->initProjectMapper();
            $this->initReceivableMapper();
            $theProject = $this->projectMapper->findById($projectId);
            $allReceivables = $this->projectMapper->getAllReceivables($theProject);

            while (list($key, $receivableId) = each($allReceivables)) {
                $eachReceivable = $this->receivableMapper->findById($receivableId);
                $requirementForReceivingField->addMultiOption($receivableId, $eachReceivable->GetTitle());
            }

            $requirementForReceivingField->setValue($setedReceivableId);

        } else throw new C3op_Projects_ActionException("Action needs a positive integer project id to find possible receivables to to be a requirement.");
   }

    private function populateResponsibleField(Zend_Form $form, $currentResponsible = 0)
    {
            $this->initContactMapper();
            $responsibleField = $form->getElement('responsible');
            $allThatCanBeResponsible = $this->contactMapper->getAllContactThatAreLinkedToAContractant();
            while (list($key, $contactId) = each($allThatCanBeResponsible)) {
                $eachPossibleResponsible = $this->contactMapper->findById($contactId);
                $responsibleField->addMultiOption($contactId, $eachPossibleResponsible->GetName());
            }
            $responsibleField->setValue($currentResponsible);
   }

    private function initProjectMapper()
    {
        if (!isset($this->projectMapper)) {
             $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
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
        if (!isset($this->contactMapper)) {
            $this->contactMapper = new C3op_Register_ContactMapper($this->db);
        }
    }

    private function initLinkageMapper()
    {
        if (!isset($this->linkageMapper)) {
            $this->linkageMapper = new C3op_Register_LinkageMapper($this->db);
        }
    }

    private function initTeamMemberMapper()
    {
        if (!isset($this->teamMemberMapper)) {
            $this->teamMemberMapper = new C3op_Projects_TeamMemberMapper($this->db);
        }
    }

    private function initReceivableMapper()
    {
        if (!isset($this->receivableMapper)) {
            $this->receivableMapper = new C3op_Projects_ReceivableMapper($this->db);
        }
    }

    private function calculateTotalValueExistentOutlays(C3op_Projects_TeamMember $h)
    {
        if (!isset($this->outlayMapper)) {
            $this->outlayMapper = new C3op_Projects_OutlayMapper($this->db);
        }

        $outlays = $this->outlayMapper->getAllOutlaysForTeamMember($h);

        $totalValue = 0;
        foreach ($outlays as $outlayId) {
            $thisOutlay = $this->outlayMapper->findById($outlayId);
            $totalValue += $thisOutlay->GetPredictedValue();
        }

        return $totalValue;
    }


    private function setDateValueToFormField(Zend_Form $form, $fieldName, $value)
    {
        $field = $form->getElement($fieldName);
        if ($value != '0000-00-00')  {
            $field->setValue(C3op_Util_DateDisplay::FormatDateToShow($value));
        } else {
            $field->setValue("");
        }
    }

     private function getTeamMembersList(C3op_Projects_Action $action)
    {

        // teamMemberList
        //   * teamMemberInfo
        //      id
        //      name
        //      description
        //      valor
        //      contractingStatus

        if (!isset($this->linkageMapper)) {
            $this->initLinkageMapper();
        }

        $teamMembersList = array();
        $teamMembersIdsList = $this->teamMemberMapper->getAllTeamMembersOnAction($action);

        foreach ($teamMembersIdsList as $teamMemberId) {
            $thisTeamMember = $this->teamMemberMapper->findById($teamMemberId);
            $currencyDisplay = new  C3op_Util_CurrencyDisplay();
            $currencyValue = $currencyDisplay->FormatCurrency($thisTeamMember->GetValue());
            $totalValueExistentOutlays = $this->calculateTotalValueExistentOutlays($thisTeamMember);

            $descriptionMessage = $thisTeamMember->GetDescription();

            $linkageId = $thisTeamMember->GetLinkage();
            $actionId = $action->GetId();
            $contactName = "(indefinido)";
            if ($linkageId > 0) {
                $this->initContactMapper();
                $this->initLinkageMapper();
                $linkageContact = $this->linkageMapper->findById($linkageId);
                $contactId = $linkageContact->GetContact();
                $contractedContact = $this->contactMapper->findById($contactId);
                $contactName = $contractedContact->GetName();
            }


            $status = $thisTeamMember->getStatus();
            $statusTypes = new C3op_Projects_TeamMemberStatusTypes();
            $statusLabel = $statusTypes->TitleForType($status);

            if ($status == C3op_Projects_TeamMemberStatusConstants::STATUS_FORESEEN) {
                $canContract = true;
                $stillNotContracted = true;
            } else {
                $canContract = false;
                $stillNotContracted = false;
            }

            $teamMembersList[$teamMemberId] = array(
                'id'                     => $teamMemberId,
                'name'                   => $contactName,
                'description'            => $descriptionMessage,
                'value'                  => $currencyValue,
                'contractingStatusLabel' => $statusLabel,
                'stillNotContractedFlag' => $stillNotContracted,
                'canContractFlag'        => $canContract,

            );
        }

        return $teamMembersList;

    }

}