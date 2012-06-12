<?php

class Projects_HumanResourceController extends Zend_Controller_Action
{
    private $humanResourceMapper;
    private $db;

    public function init()
    {
        $this->db = Zend_Registry::get('db');
    }

    public function createAction()
    {
        // cria form
        $form = new C3op_Form_HumanResourceCreate();
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
            $actionId = $data['actionId'];
            if (!isset($this->actionMapper)) {
                $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
            }
            $thisAction = $this->actionMapper->findById($actionId);
            $this->view->actionTitle = $thisAction->GetTitle();
            $actionField = $form->getElement('action');
            $actionField->setValue($actionId);
        }
    }

    public function editAction()
    {
        $form = new C3op_Form_HumanResourceEdit;
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');          
                $this->_redirect('/projects/human-resource/success-create');
            } else throw new C3op_Projects_ProjectException("Invalid data for new human resource.");
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
                if (!isset($this->humanResourceMapper)) {
                    $this->humanResourceMapper = new C3op_Projects_HumanResourceMapper($this->db);
                }
                $thisHumanResource = $this->humanResourceMapper->findById($id);
                $descriptionField = $form->getElement('description');
                $descriptionField->setValue($thisHumanResource->getDescription());
                $idField = $form->getElement('id');
                $idField->setValue($id);
                $valueField = $form->getElement('value');
                $valueField->setValue($thisHumanResource->getValue());
                $actionField = $form->getElement('action');
                $actionField->setValue($thisHumanResource->getAction());
                if (!isset($this->actionMapper)) {
                    $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
                }
                $thisAction = $this->actionMapper->findById($thisHumanResource->getAction());
                $this->view->actionTitle = $thisAction->GetTitle();
                $this->view->linkActionDetail = "/projects/action/detail/?id=" . $thisHumanResource->getAction();
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
        
        $immediateBreed = $actionMapper->getActionsSubordinatedTo($actionToBeDetailed);
        foreach ($immediateBreed as $actionId) {
            $thisAction = $actionMapper->findById($actionId);
            $nextBreed = $actionMapper->getActionsSubordinatedTo($thisAction);
            if (count($nextBreed) > 0) {
                $broodMessage = count($nextBreed) . " ações diretamente subordinadas";
                if (count($nextBreed)== 1) {
                    $broodMessage = count($nextBreed) . " ação diretamente subordinada";
                }
                $broodMessage = "<a href=/projects/action/detail/?id=" . $actionId . ">$broodMessage</a>";
            } else {
                $broodMessage = "sem ações diretamente subordinadas";
                
            }
            $actionsList[$actionId] = array(
                'id' => $actionId,
                'title' => $thisAction->GetTitle(),
                'brood' => $broodMessage,
                'linkEdit' => '/projects/action/edit/?id=' . $actionId   ,
            );
            
        }
        
        
        
        $actionInfo = array(
            'projectTitle' => $projectToBeDetailed->GetTitle(),
            'linkEditProject' => '/projects/project/edit/?id=' . $projectToBeDetailed->GetId(),
            'actionTitle' => $actionToBeDetailed->GetTitle(),
            'actionsList' => $actionsList,

        );

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
     
    private function populateSubordinatedActionsField($projectId, C3op_Form_ActionCreate $form, $actionId = 0)
    {
        $validator = new C3op_Util_ValidId();
        $parentActionId = 0;
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