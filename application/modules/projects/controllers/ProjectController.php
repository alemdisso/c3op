<?php

class Projects_ProjectController extends Zend_Controller_Action
{
    private $projectMapper;
    private $db;

    public function init()
    {
        $this->db = Zend_Registry::get('db');
        $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
    }

    public function createAction()
    {
        // cria form
        $form = new C3op_Form_ProjectCreate;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');          
                $this->_redirect('/projects/project/success-create');

            } else throw new C3op_Projects_ProjectException("A project must have a valid title.");
        }
    }

    public function editAction()
    {
        $form = new C3op_Form_ProjectEdit;
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
            // GET
            $id = $this->checkIdFromGet();
            $thisProject = $this->projectMapper->findById($id);
            $titleField = $form->getElement('title');
            $titleField->setValue($thisProject->getTitle());
            $idField = $form->getElement('id');
            $idField->setValue($id);
            $dateBeginField = $form->getElement('dateBegin');
            $dateBeginValue = $thisProject->GetDateBegin();
            if ($dateBeginValue != '0000-00-00')  {
                $dateArray = explode("-", $dateBeginValue);
                $formatedDate = $dateArray[2] . '/' . $dateArray[1] . '/' . $dateArray[0]; 
                $dateBeginField->setValue($formatedDate);
            } else {
                $dateBeginField->setValue("");
            }
            $valueField = $form->getElement('value');
            $valueField->setValue($thisProject->getValue());
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

    public function successCreateAction()
    {
        if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();    
            $this->getResponse()->setHeader('Refresh', '7; URL=/projects');
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

    public function trackAction()
    {
        $actionMapper = new C3op_Projects_ActionMapper($this->db);

        $id = $this->checkIdFromGet();
        $thisProject = $this->projectMapper->findById($id);
        $productsIdList = $this->projectMapper->getAllProducts($thisProject);
        if (count($productsIdList) > 0) {
            $linkReceivings = '/projects/project/receivings/?id=' . $thisProject->GetId();
        } else {
            $linkReceivings = "";
        }

        $actionsIdsList = $this->projectMapper->getAllActions($thisProject);
        $actionsList = array();
        reset ($actionsList);
        foreach ($actionsIdsList as $actionId) {
            $thisAction = $actionMapper->findById($actionId);
            
            if ($thisAction->GetMilestone()) {
                $milestone = "M";
            } else {
                $milestone = "";                
            }
            
            if ($thisAction->GetRequirementForReceiving()) {
                $requirementForReceiving = "$";
            } else {
                $requirementForReceiving = "";  
            }
            

            $actionsList[$actionId] = array(
                'title' => $thisAction->GetTitle(),
                'milestone' => $milestone,
                'requirementForReceiving' => $requirementForReceiving,
                'linkEdit' => '/projects/action/edit/?id=' . $actionId   ,
            );
        }
        $projectInfo = array(
            'title' => $thisProject->GetTitle(),
            'linkEdit' => '/projects/project/edit/?id=' . $id   ,
            'linkReceivings' => $linkReceivings,
            'dateBegin' => $thisProject->GetDateBegin(),
            'value' => $thisProject->GetValue(),
            'linkActionCreate' => '/projects/action/create/?project=' . $id,
            'actionsList' => $actionsList,
        );

        $this->view->projectInfo = $projectInfo;
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

    public function receivingsAction()
    {
        $actionMapper = new C3op_Projects_ActionMapper($this->db);

        $id = $this->checkIdFromGet();
        $thisProject = $this->projectMapper->findById($id);
        $productsIdList = $this->projectMapper->getAllProducts($thisProject);
        $productsList = array();
        reset ($productsList);
        foreach ($productsIdList as $actionId) {
            $thisAction = $actionMapper->findById($actionId);

            $productsList[$actionId] = array(
                'title' => $thisAction->GetTitle(),
                'linkEdit' => '/projects/action/edit/?id=' . $actionId   ,
            );
        }
        $projectInfo = array(
            'title' => $thisProject->GetTitle(),
            'linkEdit' => '/projects/project/edit/?id=' . $id   ,
            'productsList' => $productsList,
        );

        $this->view->projectInfo = $projectInfo;
    }


}