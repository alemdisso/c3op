<?php

class Projects_ActionController extends Zend_Controller_Action
{
    private $actionMapper;
    private $humanResourceMapper;
    private $projectMapper;
    private $receivingMapper;
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
                if (!isset($this->actionMapper)) {
                    $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
                }
                $parentAction = $this->actionMapper->findById($subordinatedTo);
                $projectId = $parentAction->GetProject();
            } else {
                $subordinatedTo = 0;
                $projectId = $data['project'];
            }
            $this->populateProjectFields($projectId, $form);
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
                if (!isset($this->actionMapper)) {
                    $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
                }
                $thisAction = $this->actionMapper->findById($id);
                $titleField = $form->getElement('title');
                $titleField->setValue($thisAction->getTitle());
                $idField = $form->getElement('id');
                $idField->setValue($id);
                $milestoneField = $form->getElement('milestone');
                $milestoneField->setValue($thisAction->getMilestone());
                $projectId = $this->populateProjectFields($thisAction->GetProject(), $form);
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
        $actionMapper = new C3op_Projects_ActionMapper($this->db);

        if (!isset($this->projectMapper)) {
            $this->initProjectMapper();
        }
        
        $actionToBeDetailed =  $this->initActionWithCheckedId($actionMapper);
        $projectToBeDetailed = $this->projectMapper->findById($actionToBeDetailed->getProject());
        
        if (!isset($this->humanResourceMapper)) {
            $this->humanResourceMapper = new C3op_Projects_HumanResourceMapper($this->db);
        }
        
        $humanResourcesIdsList = $this->humanResourceMapper->getAllHumanResourcesOnAction($actionToBeDetailed);
        $humanResourcesList = array();
        
        foreach ($humanResourcesIdsList as $humanResourceId) {
            $thisHumanResource = $this->humanResourceMapper->findById($humanResourceId);
            $currencyValue = C3op_Util_CurrencyDisplay::FormatCurrency($thisHumanResource->GetValue());
            
            $humanResourcesList[$humanResourceId] = array(
                'id' => $humanResourceId,
                'description' => $thisHumanResource->GetDescription(),
                'value' => $currencyValue,
                'linkEdit' => '/projects/human-resource/edit/?id=' . $humanResourceId,
            );
            
        }
        
        
        
        
        $immediateBreed = $actionMapper->getActionsSubordinatedTo($actionToBeDetailed);
        foreach ($immediateBreed as $actionId) {
            $thisAction = $actionMapper->findById($actionId);
            $nextBreed = $actionMapper->getActionsSubordinatedTo($thisAction);
            if (count($nextBreed) > 0) {
                $broodMessage = count($nextBreed) . " ações diretamente subordinadas";
                if (count($nextBreed)== 1) {
                    $broodMessage = count($nextBreed) . " ação diretamente subordinada";
                }
            } else {
                $broodMessage = "sem ações diretamente subordinadas";
                
            }
            $actionTitle =  sprintf("<a href=/projects/action/detail/?id=%d>%s</a>", $actionId, $thisAction->GetTitle());
            $actionsList[$actionId] = array(
                'id' => $actionId,
                'title' => $actionTitle,
                'brood' => $broodMessage,
                'linkEdit' => '/projects/action/edit/?id=' . $actionId   ,
            );
            
        }
        

        $actionInfo = array(
            'projectTitle' => $projectToBeDetailed->GetTitle(),
            'projectDetailLink' => '/projects/project/detail/?id=' . $projectToBeDetailed->GetId(),
            'linkEditProject' => '/projects/project/edit/?id=' . $projectToBeDetailed->GetId(),
            'actionTitle' => $actionToBeDetailed->GetTitle(),
            'actionsList' => $actionsList,
            'humanResourcesList' => $humanResourcesList,            
            'id' => $actionToBeDetailed->GetId(),
            'linkActionCreate' => '/projects/action/create/?subordinatedTo=' . $actionToBeDetailed->GetId(),

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
        throw new C3op_Projects_ProjectException("Invalid Project Id from Get");

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
     
    private function initProjectMapper()
    {
         $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
    }
    
    
}