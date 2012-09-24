<?php

class Projects_ActionController extends Zend_Controller_Action
{
    private $actionMapper;
    private $humanResourceMapper;
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
                $this->_redirect('/projects/action/success-create/?id=' . $id);
            } else {
                //form error: populate and go back
                $form->populate($postData);
                $this->view->form = $form;
            }
        } else {
            $data = $this->_request->getParams();
            if (isset($data['subordinatedTo'])) {
                $subordinatedTo = $data['subordinatedTo'];
                $this->initActionMapper();
                $parentAction = $this->actionMapper->findById($subordinatedTo);
                $projectId = $parentAction->GetProject();
            } else {
                $subordinatedTo = 0;
                $projectId = $data['project'];
            }
            $this->populateProjectFields($projectId, $form);
            $this->populateResponsibleField($form);
            $this->populateRequirementForReceivingField($projectId, $form);
            $this->populateSubordinatedActionsField($projectId, $form, 0, $subordinatedTo);
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
                $this->_redirect('/projects/action/success-create/?id=' . $id);
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

                $element = $form->getElement('status');
                $element->setValue($inputAction->getStatus());

                $this->populateRequirementForReceivingField($projectId, $form, $inputAction->getRequirementForReceiving());
                $this->populateSubordinatedActionsField($projectId, $form, $id);
            }

        }
    }

    public function sucessAction()
    {
        if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $this->view->messages = $this->_helper
                ->getHelper('FlashMessenger')
                ->getMessages();
        } else {
            $this->_redirect('/');
        }
    }

    public function detailAction()
    {
        $pageData = array();

        $actionsList = array();
        $this->initActionMapper();
        $this->initProjectMapper();
        $this->initHumanResourceMapper();

        $actionToBeDetailed =  $this->initActionWithCheckedId($this->actionMapper);
        $projectToBeDetailed = $this->projectMapper->findById($actionToBeDetailed->getProject());


        // humanResourceList
        //   * humanResourceInfo
        //      id
        //      name
        //      description
        //      valor
        //      contractingStatus

        $humanResourcesList = $this->GetHumanResourcesList($actionToBeDetailed);

        $immediateBreed = $this->actionMapper->getActionsSubordinatedTo($actionToBeDetailed);
        foreach ($immediateBreed as $actionId) {
            $loopAction = $this->actionMapper->findById($actionId);
            $nextBreed = $this->actionMapper->getActionsSubordinatedTo($loopAction);
            if (count($nextBreed) > 0) {
                $broodMessage = count($nextBreed) . " ações diretamente subordinadas";
                if (count($nextBreed)== 1) {
                    $broodMessage = count($nextBreed) . " ação diretamente subordinada";
                }
            } else {
                $broodMessage = "sem ações diretamente subordinadas";

            }

            $rejectLink = $this->manageRejectReceiptLink($loopAction);

            $actionTitle =  sprintf("<a href='/projects/action/detail/?id=%d'>%s</a>", $actionId, $loopAction->GetTitle());
            $actionsList[$actionId] = array(
                'id'         => $actionId,
                'title'      => $actionTitle,
                'brood'      => $broodMessage,
                'editLink'   => '/projects/action/edit/?id=' . $actionId,
                'done'       => $loopAction->GetDone(),
                'finishDate' => C3op_Util_DateDisplay::FormatDateToShow($loopAction->GetRealFinishDate()),
                'status'     => $loopAction->GetStatus(),
                'rejectLink' => $rejectLink,
                );

        }

        $id = $actionToBeDetailed->GetId();
        $msgStart = "";
        $msgAcknowledgement = "";
        $linkAcknowledgement = "";
        if ($actionToBeDetailed->GetStatus() == C3op_Projects_ActionStatusConstants::STATUS_IN_EXECUTION) {
//            $msgStart = "Iniciada em " . $actionToBeDetailed->GetRealBeginDate();
            $msgStart = $actionToBeDetailed->GetRealBeginDate();

            $obj = new C3op_Projects_ActionStartMode($actionToBeDetailed, $this->actionMapper);
            if ($obj->isUnacknowledged()) {
                $msgAcknowledgement = "Confirmar";
                $linkAcknowledgement =  "javascript:passIdToAjax('/projects/action/acknowledge-start', '$id', acknowledgeStartResponse);";
            }
        }

        $msgDone = "";
        $linkDone = "";
        if ($actionToBeDetailed->GetStatus() == C3op_Projects_ActionStatusConstants::STATUS_DONE) {
            $msgDone = "Ação realizada";
            $linkDone = "";
            $acceptLink = "";
        } elseif ($actionToBeDetailed->GetStatus() == C3op_Projects_ActionStatusConstants::STATUS_RECEIVED) {
            $msgDone = "Ação recebida em " . $actionToBeDetailed->GetReceiptDate($this->actionMapper);
            $linkDone = "";
            $acceptLink = $this->manageAcceptanceLink($actionToBeDetailed);
        } else {
            $msgDone = "Confirma que ação foi entregue ao IETS";
            $linkDone = "javascript:passIdToAjax('/projects/action/acknowledge-receipt', '$id', acknowledgeReceiptResponse);";
        }

        $rejectLink = $this->manageRejectReceiptLink($actionToBeDetailed);
        $acceptLink = $this->manageAcceptanceLink($actionToBeDetailed);



        //  actionInfo
        //    id
        //    projectName
        //    name
        //    status
        //    relationship
//        $actionInfo = array(
//                'id'           => $actionToBeDetailed->getId(),
//                'projectTitle'  => "Estou Seguro",
//                'title'         => $actionToBeDetailed->GetTitle(),
//                'relationship' => C3op_Register_ContactTypes::TitleForType($contactBeingDetailed->GetType())
//            );

        $actionInfo = array(
            'id'           => $actionToBeDetailed->getId(),
            'projectTitle'  => "###Estou Seguro",
            'title'         => $actionToBeDetailed->GetTitle(),
            //'projectTitle'       => $projectToBeDetailed->GetTitle(),
            'projectDetailLink'  => '/projects/project/detail/?id=' . $projectToBeDetailed->GetId(),
            'editLinkProject'    => '/projects/project/edit/?id=' . $projectToBeDetailed->GetId(),
            'actionTitle'        => $actionToBeDetailed->GetTitle(),
            'actionsList'        => $actionsList,
            'humanResourcesList' => $humanResourcesList,
            'id'                 => $actionToBeDetailed->GetId(),
            'linkActionCreate'   => '/projects/action/create/?subordinatedTo=' . $actionToBeDetailed->GetId(),
            'editLink'           => '/projects/action/edit/?id=' . $actionToBeDetailed->GetId(),
            'linkDone'           => $linkDone,
            'rejectLink'         => $rejectLink,
            'acceptLink'         => $acceptLink,
            'finishDate' => C3op_Util_DateDisplay::FormatDateToShow($actionToBeDetailed->GetRealFinishDate()),
            'msgDone'            => $msgDone,
            'msgStart'            => $msgStart,
            'linkAcknowledgement'           => $linkAcknowledgement,
            'msgAcknowledgement'            => $msgAcknowledgement,
        );
        if ($actionToBeDetailed->GetSubordinatedTo() > 0) {
            $actionInfo['parentLink'] = '/projects/action/detail/?id=' . $actionToBeDetailed->GetSubordinatedTo();
            if (!isset($this->actionMapper)) {
                $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
            }
            $parent = $this->actionMapper->FindById($actionToBeDetailed->GetSubordinatedTo());
            $actionInfo['parentTitle'] = $parent->GetTitle();
        }


        $pageData = array(
                      'actionInfo' => $actionInfo,
                      'humanResourcesList' => $humanResourcesList,
                    );

        $this->view->pageData = $pageData;

        $this->view->actionInfo = $actionInfo;
    }

    public function treeAction()
    {
        $this->initActionMapper();
        $action =  $this->initActionWithCheckedId($this->actionMapper);

        $objTree = new C3op_Projects_ActionTree();
        $tree = $objTree->retrieveTree($action, $this->actionMapper);

        $this->treeData = array();
        $this->fillDataTree($tree);


        $this->view->actionTree = $tree;
        $this->view->treeData = $this->treeData;

    }


    public function successCreateAction()
    {
        $this->initActionMapper();
        $action =  $this->initActionWithCheckedId($this->actionMapper);
        if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();
            $this->getResponse()->setHeader('Refresh', '3; URL=/projects/action/detail/?id=' . $action->getId());
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

    public function acknowledgeReceiptAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $this->initActionMapper();
        $actionToBeChanged =  $this->initActionWithCheckedId($this->actionMapper);

        $acknowledgment = new C3op_Projects_ReceiptAcknowledgment();
        $acknowledgment->AcknowledgeReceipt($actionToBeChanged, $this->actionMapper);

        echo 'Ação Recebida';
    }

   public function acceptReceiptAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $this->initActionMapper();
        $actionToBeChanged =  $this->initActionWithCheckedId($this->actionMapper);
        $acceptance = new C3op_Projects_ReceiptAcceptance();
        $acceptance->AcceptReceipt($actionToBeChanged, $this->actionMapper);

        echo 'Realização da tarefa confirmada';
    }

   public function rejectReceiptAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $this->initActionMapper();
        $actionToBeChanged =  $this->initActionWithCheckedId($this->actionMapper);
        $rejection = new C3op_Projects_ReceiptRejection();
        $rejection->RejectReceipt($actionToBeChanged, $this->actionMapper);

        echo 'Entrega rejeitada';
    }

    public function acknowledgeStartAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $this->initActionMapper();
        $actionToBeChanged =  $this->initActionWithCheckedId($this->actionMapper);

        $acknowledgment = new C3op_Projects_ActionAcknowledgeStart($actionToBeChanged);

        echo 'Confirmado';
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
            $this->view->projectTitle = $theProject->GetTitle();
            $this->view->linkProjectDetail = "/projects/project/detail/?id=" . $theProject->GetId();

            return $projectId;
        } else throw new C3op_Projects_ActionException(_("#It needs a positive integer project id to populate project fields."));

   }

    private function populateSubordinatedActionsField($projectId, C3op_Form_ActionCreate $form, $actionId = 0, $parentActionId = 0)
    {
        $validator = new C3op_Util_ValidId();
        if ($validator->isValid($projectId)) {
            $subordinatedToField = $form->getElement('subordinatedTo');
            if (!isset($this->actionMapper)) {
                $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
            }

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
            if (!isset($this->projectMapper)) {
                $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
            }
            if (!isset($this->receivableMapper)) {
                $this->receivableMapper = new C3op_Projects_ReceivableMapper($this->db);
            }
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
         $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
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

    private function initHumanResourceMapper()
    {
        if (!isset($this->humanResourceMapper)) {
            $this->humanResourceMapper = new C3op_Projects_HumanResourceMapper($this->db);
        }
    }

    private function calculateTotalValueExistentOutlays(C3op_Projects_HumanResource $h)
    {
        if (!isset($this->outlayMapper)) {
            $this->outlayMapper = new C3op_Projects_OutlayMapper($this->db);
        }

        $outlays = $this->outlayMapper->getAllOutlaysForHumanResource($h);

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

     private function getHumanResourcesList(C3op_Projects_Action $action)
    {

        // humanResourceList
        //   * humanResourceInfo
        //      id
        //      name
        //      description
        //      valor
        //      contractingStatus



        $humanResourcesList = array();
        $humanResourcesIdsList = $this->humanResourceMapper->getAllHumanResourcesOnAction($action);

        foreach ($humanResourcesIdsList as $humanResourceId) {
            $thisHumanResource = $this->humanResourceMapper->findById($humanResourceId);
            $currencyValue = C3op_Util_CurrencyDisplay::FormatCurrency($thisHumanResource->GetValue());
            $totalValueExistentOutlays = $this->calculateTotalValueExistentOutlays($thisHumanResource);

            $descriptionMessage = $thisHumanResource->GetDescription();

            $contactId = $thisHumanResource->GetContact();
            $actionId = $action->GetId();
            $contactName = "(indefinido)";
            if ($contactId > 0) {
                $this->initContactMapper();
                $contractedContact = $this->contactMapper->findById($contactId);
                $contactName = $contractedContact->GetName();
            }

            $dismissalLink = $this->manageDismissalLink($thisHumanResource);

            $contractingLink = $this->manageContractingLink($thisHumanResource);

            $contractedLabel = "";
            if ($thisHumanResource->GetStatus() == C3op_Projects_HumanResourceStatusConstants::STATUS_CONTRACTED) {
                $contractedLabel = "Recurso contratado";
            }

            $humanResourcesList[$humanResourceId] = array(
                'id' => $humanResourceId,
                'contactName' => $contactName,
                'description' => $descriptionMessage,
                'value' => $currencyValue,
                'contractingStatus' => $contractedLabel,
                'linkOutlays' => '/projects/human-resource/outlays/?id=' . $humanResourceId,
                'totalOutlays' => $totalValueExistentOutlays,
                'dismissalLink' => $dismissalLink,
                'contractingLink' => $contractingLink,
                'contractedLabel' => $contractedLabel,
            );
        }

        return $humanResourcesList;

    }

    private function manageRejectReceiptLink(C3op_Projects_Action $action) {
        $rejectLink = "";
        if ($action->GetStatus() == C3op_Projects_ActionStatusConstants::STATUS_RECEIVED) {
            $rejectLink = sprintf("javascript:passIdToAjax('/projects/action/reject-receipt', %d, rejectReceiptResponse)", $action->GetId());
        }
        return $rejectLink;
    }

    private function manageAcceptanceLink(C3op_Projects_Action $action) {
        $acceptLink = "";
        if ($action->GetStatus() == C3op_Projects_ActionStatusConstants::STATUS_RECEIVED) {
            $acceptLink = sprintf("javascript:passIdToAjax('/projects/action/accept-receipt', %d, acceptReceiptResponse)", $action->GetId());
        }
        return $acceptLink;
    }

    private function manageDismissalLink(C3op_Projects_HumanResource $humanResource) {
        $dismissalLink = "";
        if (($humanResource->GetContact() > 0)
                && ($humanResource->GetStatus() == C3op_Projects_HumanResourceStatusConstants::STATUS_FORESEEN)) {
            $dismissalLink = sprintf("javascript:passIdToAjax('/projects/human-resource/dismiss-contact', %d, dismissContactResponse)", $humanResource->GetId());
        }
        return $dismissalLink;
    }

    private function manageContractingLink(C3op_Projects_HumanResource $humanResource) {
        $contractingLink = "";
        if (($humanResource->GetContact() > 0)
           && ($humanResource->GetStatus() == C3op_Projects_HumanResourceStatusConstants::STATUS_FORESEEN)) {
            $contractingLink = sprintf("javascript:passIdToAjax('/projects/human-resource/contract-contact', %d, contractContactResponse)", $humanResource->GetId());
        }
        return $contractingLink;
    }

    private function fillDataTree($tree)
    {
        $this->initActionMapper();
        foreach ($tree as $id => $subTree) {
            $loopAction = $this->actionMapper->findById($id);
            $data = array();
            $data["title"] = $loopAction->GetTitle();

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