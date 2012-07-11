<?php

class Projects_ActionController extends Zend_Controller_Action
{
    private $actionMapper;
    private $humanResourceMapper;
    private $projectMapper;
    private $receivingMapper;
    private $contactMapper;
    private $db;

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
                $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');          
                $this->_redirect('/projects/action/success-create');
            } else throw new C3op_Projects_ActionException("An action must have a valid title.");
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
            $this->PopulateResponsibleField($form);
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
                $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');          
                $this->_redirect('/projects/project/success-create');
            } else throw new C3op_Projects_ProjectException("A project must have a valid title.");
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
                $thisAction = $this->actionMapper->findById($id);
                $titleField = $form->getElement('title');
                $titleField->setValue($thisAction->getTitle());
                $idField = $form->getElement('id');
                $idField->setValue($id);
                $milestoneField = $form->getElement('milestone');
                $milestoneField->setValue($thisAction->getMilestone());
                $projectId = $this->populateProjectFields($thisAction->GetProject(), $form);
                $descriptionField = $form->getElement('description');
                $descriptionField->setValue($thisAction->GetDescription());
                $this->SetDateValueToFormField($form, 'predictedBeginDate', $thisAction->GetPredictedBeginDate());
                $this->SetDateValueToFormField($form, 'predictedFinishDate', $thisAction->GetPredictedFinishDate());
                $statusField = $form->getElement('status');
                $statusField->setValue($thisAction->GetStatus());
                $this->populateRequirementForReceivingField($projectId, $form, $thisAction->GetRequirementForReceiving());
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
        $actionsList = array();
        $this->initActionMapper();
        $this->initProjectMapper();
        $this->initHumanResourceMapper();

        $actionToBeDetailed =  $this->initActionWithCheckedId($this->actionMapper);
        $projectToBeDetailed = $this->projectMapper->findById($actionToBeDetailed->getProject());
        
        $humanResourcesList = $this->GetHumanResourcesList($actionToBeDetailed);
        
        $immediateBreed = $this->actionMapper->getActionsSubordinatedTo($actionToBeDetailed);
        foreach ($immediateBreed as $actionId) {
            $thisAction = $this->actionMapper->findById($actionId);
            $nextBreed = $this->actionMapper->getActionsSubordinatedTo($thisAction);
            if (count($nextBreed) > 0) {
                $broodMessage = count($nextBreed) . " ações diretamente subordinadas";
                if (count($nextBreed)== 1) {
                    $broodMessage = count($nextBreed) . " ação diretamente subordinada";
                }
            } else {
                $broodMessage = "sem ações diretamente subordinadas";
                
            }
            
            $rejectLink = $this->ManageRejectionLink($thisAction);
            
            $actionTitle =  sprintf("<a href='/projects/action/detail/?id=%d'>%s</a>", $actionId, $thisAction->GetTitle());
            $actionsList[$actionId] = array(
                'id'         => $actionId,
                'title'      => $actionTitle,
                'brood'      => $broodMessage,
                'editLink'   => '/projects/action/edit/?id=' . $actionId,
                'done'       => $thisAction->GetDone(),
                'finishDate' => C3op_Util_DateDisplay::FormatDateToShow($thisAction->GetRealFinishDate()),
                'status'     => $thisAction->GetStatus(),
                'rejectLink' => $rejectLink,
                );
            
        }
        
        $id = $actionToBeDetailed->GetId();
        
        if ($actionToBeDetailed->GetDone()) {
            $msgDone = "Ação realizada";
            $linkDone = "";
        } else {
            $msgDone = "Confirma realização da ação";
            $linkDone = "javascript:passIdToAjax('/projects/action/confirm-realization', '$id', confirmRealizationResponse);";
        }
            
        $rejectLink = $this->ManageRejectionLink($actionToBeDetailed);
        

        $actionInfo = array(
            'projectTitle'       => $projectToBeDetailed->GetTitle(),
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
            'msgDone'            => $msgDone,
        );
        if ($actionToBeDetailed->GetSubordinatedTo() > 0) {
            $actionInfo['parentLink'] = '/projects/action/detail/?id=' . $actionToBeDetailed->GetSubordinatedTo();
            if (!isset($this->actionMapper)) {
                $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
            }
            $parent = $this->actionMapper->FindById($actionToBeDetailed->GetSubordinatedTo());
            $actionInfo['parentTitle'] = $parent->GetTitle();
        }
        
        

        $this->view->actionInfo = $actionInfo;
    }
    

    public function successCreateAction()
    {
        if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();    
            $this->getResponse()->setHeader('Refresh', '3; URL=/projects');
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
    
    public function confirmRealizationAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $id = $_REQUEST['id'];

        $this->initActionMapper();
        $actionToBeChanged =  $this->initActionWithCheckedId($this->actionMapper);
        
        $realization = new C3op_Projects_ActionRealization();
        $realization->ConfirmRealization($actionToBeChanged, $this->actionMapper);

        echo 'Ação Realizada';
    }  
  
    
   public function rejectDeliveryAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $this->initActionMapper();        
        $actionToBeChanged =  $this->initActionWithCheckedId($this->actionMapper);
        $rejection = new C3op_Projects_ActionRejection();
        $rejection->RejectDelivery($actionToBeChanged, $this->actionMapper);

        echo 'Entrega rejeitada';
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
        throw new C3op_Projects_ActionException("Invalid Project Id from Get");

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
            $thisProject = $this->projectMapper->findById($projectId);
            $this->view->projectTitle = $thisProject->GetTitle();
            $this->view->linkProjectDetail = "/projects/project/detail/?id=" . $thisProject->GetId();

            return $projectId;
        } else throw new C3op_Projects_ActionException("Action needs a positive integer project id.");
        
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
                $thisAction = $this->actionMapper->findById($actionId);
                $parentActionId = $thisAction->GetSubordinatedTo();
                $allOtherActionsInProject = $this->actionMapper->getAllOtherActions($thisAction);
                
            } else {
                if (!isset($this->projectMapper)) {
                    $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
                }
                $thisProject = $this->projectMapper->findById($projectId);
                $allOtherActionsInProject = $this->projectMapper->getAllActions($thisProject);
            }

            while (list($key, $actionId) = each($allOtherActionsInProject)) {
                $eachAction = $this->actionMapper->findById($actionId);
                $subordinatedToField->addMultiOption($actionId, $eachAction->GetTitle());
            }
            
            $subordinatedToField->setValue($parentActionId);
        
        } else throw new C3op_Projects_ActionException("Action needs a positive integer project id to find other actions.");
   }
     
    private function populateRequirementForReceivingField($projectId, C3op_Form_ActionCreate $form, $setedReceivingId = 0)
    {
        $validator = new C3op_Util_ValidId();
        if ($validator->isValid($projectId)) {
            $requirementForReceivingField = $form->getElement('requirementForReceiving');
            if (!isset($this->projectMapper)) {
                $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
            }
            if (!isset($this->receivingMapper)) {
                $this->receivingMapper = new C3op_Projects_ReceivingMapper($this->db);
            }
            $thisProject = $this->projectMapper->findById($projectId);
            $allReceivings = $this->projectMapper->getAllReceivings($thisProject);

            while (list($key, $receivingId) = each($allReceivings)) {
                $eachReceiving = $this->receivingMapper->findById($receivingId);
                $requirementForReceivingField->addMultiOption($receivingId, $eachReceiving->GetTitle());
            }
            
            $requirementForReceivingField->setValue($setedReceivingId);
        
        } else throw new C3op_Projects_ActionException("Action needs a positive integer project id to find possible receivings to to be a requirement.");
   }
     
    private function PopulateResponsibleField(Zend_Form $form, $currentResponsible = 0)
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
        $humanResourcesList = array();
        $humanResourcesIdsList = $this->humanResourceMapper->getAllHumanResourcesOnAction($action);
        
        foreach ($humanResourcesIdsList as $humanResourceId) {
            $thisHumanResource = $this->humanResourceMapper->findById($humanResourceId);
            $currencyValue = C3op_Util_CurrencyDisplay::FormatCurrency($thisHumanResource->GetValue());
            $totalValueExistentOutlays = $this->calculateTotalValueExistentOutlays($thisHumanResource);
            
            $descriptionMessage = $thisHumanResource->GetDescription();
            
            $contactId = $thisHumanResource->GetContact();
            if ($contactId > 0) {
                $this->initContactMapper();
                $contractedContact = $this->contactMapper->findById($contactId);
                $contactName = $contractedContact->GetName();
                if ($descriptionMessage != "") {
                    $descriptionMessage = "$contactName: $descriptionMessage";
                } else {
                    $descriptionMessage = "$contactName";
                    
                }
                
            }
            
            $humanResourcesList[$humanResourceId] = array(
                'id' => $humanResourceId,
                'description' => $descriptionMessage,
                'value' => $currencyValue,
                'editLink' => '/projects/human-resource/edit/?id=' . $humanResourceId,
                'linkCreateOutlay' => '/projects/outlay/create/?humanResource=' . $humanResourceId,
                'linkOutlays' => '/projects/human-resource/outlays/?id=' . $humanResourceId,
                'totalOutlays' => $totalValueExistentOutlays,
            );
        }
        
        return $humanResourcesList;

    }
    
    private function ManageRejectionLink(C3op_Projects_Action $action) {
        $rejectLink = "";
        if ($action->GetDone()) {
            $rejectLink = sprintf("javascript:passIdToAjax('/projects/action/reject-delivery', %d, rejectDeliveryResponse)", $action->GetId());
        }
        return $rejectLink;
            
        
    }
    
    
}