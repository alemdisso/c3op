<?php

class Auth_UserController extends Zend_Controller_Action
{
    private $userMapper;
    private $db;

    public function init()
    {
        $this->db = Zend_Registry::get('db');
        $this->userMapper = new C3op_Auth_UserMapper($this->db);
    }

    public function createAction()
    {
        // cria form
        $form = new C3op_Form_UserCreate;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');          
                $this->_redirect('/auth/user/success-create');

            } else throw new C3op_Auth_UserException("A user must have a valid title.");
        } else {

        }
    }

    public function editAction()
    {
        $form = new C3op_Form_UserEdit;
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            try {
                if ($form->isValid($postData)) {
                    $form->process($postData);
                    $this->_helper->getHelper('FlashMessenger')
                        ->addMessage('The record was successfully updated.');          
                    $this->_redirect('/auth/user/success-create');
                }
            } catch (Exception $e) {
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($e->getMessage());          
                $this->view->form = $form;
                return $this->render('create');
                
            }
        } else {
            // GET
            $thisUser = $this->InitUserWithCheckedId($this->userMapper);
            $id = $this->checkIdFromGet();
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $id);
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'name', $thisUser->GetName());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'login', $thisUser->GetLogin());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'email', $thisUser->GetEmail());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'role', $thisUser->GetRole());
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
            $this->getResponse()->setHeader('Refresh', '3; URL=/');
        } else {
            $this->_redirect('/');    
        } 
    }

    public function errorEditAction()
    {
        $flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $flashMessenger->setNamespace('messages');
        $this->view->messages = $flashMessenger->getMessages();
        $flashMessenger->addMessage('Id Inválido');
    }


    private function initUserMapper()
    {
         $this->userMapper = new C3op_Auth_UserMapper($this->db);
    }
    
    private function InitUserWithCheckedId(C3op_Auth_UserMapper $mapper)
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
        throw new C3op_Auth_UserException("Invalid User Id from Get");

    }

    
}