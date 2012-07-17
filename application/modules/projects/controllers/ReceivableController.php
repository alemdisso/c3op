<?php

class Projects_ReceivableController extends Zend_Controller_Action
{
    private $receivableMapper;
    private $projectMapper;
    private $db;

    public function init()
    {
        $this->db = Zend_Registry::get('db');
    }

    public function createAction()
    {
        // cria form
        $form = new C3op_Form_ReceivableCreate;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');          
                $this->_redirect('/projects/receivable/success-create');
            } else throw new C3op_Projects_ReceivableException("An receivable must have a valid title.");
        } else {
            $data = $this->_request->getParams();
            $projectId = $data['project'];
            $this->PopulateProjectFields($projectId, $form);
        }
    }

    public function editAction()
    {
        $form = new C3op_Form_ReceivableEdit;
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
                if (!isset($this->receivableMapper)) {
                    $this->receivableMapper = new C3op_Projects_ReceivableMapper($this->db);
                }
                $thisReceivable = $this->receivableMapper->findById($id);
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'title', $thisReceivable->GetTitle());
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $id);
                $this->SetDateValueToFormField($form, 'predictedDate', $thisReceivable->GetPredictedDate());
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'predictedValue', $thisReceivable->GetPredictedValue());
                $this->SetDateValueToFormField($form, 'realDate', $thisReceivable->GetRealDate());
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'realValue', $thisReceivable->GetRealValue());
                $projectId = $this->populateProjectFields($thisReceivable->GetProject(), $form);
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
    
    private function PopulateProjectFields($projectId, C3op_Form_ReceivableCreate $form)
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
            $this->view->projectId = $projectId;
            return $projectId;
        } else throw new C3op_Projects_ReceivableException("Receivable needs a positive integer project id.");

    }

    private function setDateValueToFormField(C3op_Form_ReceivableCreate $form, $fieldName, $value)
    {
        $field = $form->getElement($fieldName);
        if (($value != '0000-00-00') && ($value != "")) {
            $field->setValue($this->formatDataToShow($value));
        } else {
            $field->setValue("");
        }
    }

    private function formatDataToShow($rawData)
    {
        $dateArray = explode("-", $rawData);
        $formatedDate = $dateArray[2] . '/' . $dateArray[1] . '/' . $dateArray[0]; 
        return $formatedDate;
    }
    
}