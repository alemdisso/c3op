<?php

class Projects_ProjectController extends Zend_Controller_Action
{
    private $projectMapper;

    public function init()
    {
        $db = Zend_Registry::get('db');
        $this->projectMapper = new C3op_Projects_ProjectMapper($db);        
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

            } else {
                throw new C3op_Projects_ProjectException('Invalid id!');
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