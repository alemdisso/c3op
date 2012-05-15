<?php

class Projects_ActionController extends Zend_Controller_Action
{
    private $actionMapper;
    private $projectMapper;
    private $db;

    public function init()
    {
        $this->db = Zend_Registry::get('db');
        $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
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
            $validator = new C3op_Util_ValidId();
            $projectId = $data['project'];
            if ($validator->isValid($projectId)) {
                $projectField = $form->getElement('project');
                $projectField->setValue($projectId);
                if (!isset($this->projectMapper)) {
                        $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
                }

                $thisProject = $this->projectMapper->findById($projectId);
                $this->view->projectTitle = $thisProject->GetTitle();
            } else throw new C3op_Projects_ActionException("Action needs a positive integer project id.");
            
            
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
    
    
}