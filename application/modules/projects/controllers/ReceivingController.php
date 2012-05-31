<?php

class Projects_ReceivingController extends Zend_Controller_Action
{
    private $receivingMapper;
    private $projectMapper;
    private $db;

    public function init()
    {
        $this->db = Zend_Registry::get('db');
    }

    public function createAction()
    {
        // cria form
        $form = new C3op_Form_ReceivingCreate;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');          
                $this->_redirect('/projects/receiving/success-create');
            } else throw new C3op_Projects_ReceivingException("An receiving must have a valid title.");
        } else {
            $data = $this->_request->getParams();
            $projectId = $data['project'];
            $this->PopulateProjectFields($projectId, $form);
        }
    }

    public function editAction()
    {
        $form = new C3op_Form_ReceivingEdit;
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
                if (!isset($this->receivingMapper)) {
                    $this->receivingMapper = new C3op_Projects_ReceivingMapper($this->db);
                }
                $thisReceiving = $this->receivingMapper->findById($id);
                $this->SetValueToFormField($form, 'name', $thisReceiving->GetName());
                $this->SetValueToFormField($form, 'id', $id);
                $this->SetValueToFormField($form, 'predictedDate', $thisReceiving->GetPredictedDate());
                $this->SetValueToFormField($form, 'predictedValue', $thisReceiving->GetPredictedValue());
                $this->SetValueToFormField($form, 'realDate', $thisReceiving->GetRealDate());
                $this->SetValueToFormField($form, 'realValue', $thisReceiving->GetRealValue());
                $projectId = $this->populateProjectFields($thisReceiving->GetProject(), $form);
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
    
    private function PopulateProjectFields($projectId, C3op_Form_ReceivingCreate $form)
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
            return $projectId;
        } else throw new C3op_Projects_ReceivingException("Receiving needs a positive integer project id.");

    }

    private function setValueToFormField(C3op_Form_ReceivingCreate $form, $fieldName, $value)
    {
        $field = $form->getElement($fieldName);
        $field->setValue($value);
    }

    
}