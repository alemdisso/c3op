<?php

class Projects_ReceivableController extends Zend_Controller_Action
{
    private $receivableMapper;
    private $projectMapper;
    private $db;

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
        $form = new C3op_Form_ReceivableCreate;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/projects/receivable/success/?id=' . $postData['project']);
            } else {
                //form error: populate and go back
                $form->populate($postData);
                $this->view->form = $form;
            }
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
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/projects/project/success');
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
                if (!isset($this->receivableMapper)) {
                    $this->receivableMapper = new C3op_Projects_ReceivableMapper($this->db);
                }
                $thisReceivable = $this->receivableMapper->findById($id);
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'title', $thisReceivable->GetTitle());
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $id);
                $this->SetDateValueToFormField($form, 'predictedDate', $thisReceivable->GetPredictedDate());
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'predictedValue', $thisReceivable->GetPredictedValue());
//                $this->SetDateValueToFormField($form, 'realDate', $thisReceivable->GetRealDate());
//                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'realValue', $thisReceivable->GetRealValue());
                $projectId = $this->populateProjectFields($thisReceivable->GetProject(), $form);
            }

        }
    }

    public function successAction()
    {
        $this->initProjectMapper();
        $projectRelated =  $this->initProjectWithCheckedId($this->projectMapper);

        if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();
            $this->getResponse()->setHeader('Refresh', '1; URL=/projects/project/detail/?id=' . $projectRelated->getId());
        } else {
            $this->_redirect('/projects');
        }
    }

    public function notifyAction()
    {
        // cria form
        $form = new C3op_Form_ReceivableNotify;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/projects/receivable/success/?id=' . $postData['project']);
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
                $this->initReceivableMapper();
                $receivableToBeNotified =  $this->initReceivableWithCheckedId($this->receivableMapper);
                $this->view->title = $receivableToBeNotified->GetTitle();
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $receivableToBeNotified->getId());
//                $this->SetDateValueToFormField($form, 'realDate', $thisReceivable->GetRealDate());
//                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'realValue', $thisReceivable->GetRealValue());
                $projectId = $this->populateProjectFields($receivableToBeNotified->GetProject(), $form);
            }

        }
    }

    private function PopulateProjectFields($projectId, Zend_Form $form)
    {
        $validator = new C3op_Util_ValidId();
        if ($validator->isValid($projectId)) {
            $projectField = $form->getElement('project');
            $projectField->setValue($projectId);
            $this->initProjectMapper();
            $thisProject = $this->projectMapper->findById($projectId);
            $this->view->projectTitle = $thisProject->GetShortTitle();
            $this->view->projectId = $projectId;
            return $projectId;
        } else throw new C3op_Projects_ReceivableException("Receivable needs a positive integer project id.");

    }

    private function initProjectMapper()
    {
        if (!isset($this->projectMapper)) {
            $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
        }
    }

    private function initReceivableMapper()
    {
        if (!isset($this->receivableMapper)) {
            $this->receivableMapper = new C3op_Projects_ReceivableMapper($this->db);
        }
    }

    private function initReceivableWithCheckedId(C3op_Projects_ReceivableMapper $mapper)
    {
        return $mapper->findById($this->checkIdFromGet());
    }

    private function initProjectWithCheckedId(C3op_Projects_ProjectMapper $mapper)
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
        throw new C3op_Projects_OutlayException("Invalid Action Id from Get");

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

}