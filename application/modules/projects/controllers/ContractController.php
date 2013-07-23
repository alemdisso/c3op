<?php

class Projects_ContractController extends Zend_Controller_Action
{
    private $contractMapper;
    private $projectMapper;
    private $db;

    public function preDispatch()
    {
        try {
            $checker = new C3op_Access_PrivilegeChecker();
        } catch (Exception $e) {
            throw $e;
        }
        $this->view->pageUri = "";
        $this->view->pageTitle = "";


    }

    public function postDispatch()
    {
        $trail = new C3op_Util_Breadcrumb();
        if (isset($this->view->pageTitle)) {
            $breadcrumb = $trail->add($this->view->pageTitle, $this->view->pageUri);
        }
    }

    public function init()
    {
        $this->db = Zend_Registry::get('db');
    }

    public function createAction()
    {
        // cria form
        $form = new C3op_Form_ContractCreate;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/projects/contract/detail/?id=' . $id);
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
        $form = new C3op_Form_ContractEdit;
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/projects/contract/detail/?id=' . $id);
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
                $this->initContractMapper();
                $thisContract = $this->contractMapper->findById($id);
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $id);
                $this->SetDateValueToFormField($form, 'signingDate', $thisContract->GetSigningDate());
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'amendment', $thisContract->GetAmendment());
                $projectId = $this->populateProjectFields($thisContract->GetProject(), $form);
            }

        }
    }



    public function signAction()
    {
        $form = new C3op_Form_ContractSign;
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->initContractMapper();
                $this->initProjectMapper();
                $contractObj = $this->contractMapper->findById($id);
                $projectObj = $this->projectMapper->findById($contractObj->getProject());



                $this->_redirect('/projects/contract/detail/?id=' . $id);
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
        } else throw new C3op_Projects_ContractException("Contract needs a positive integer project id.");

    }

    private function initProjectMapper()
    {
        if (!isset($this->projectMapper)) {
            $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
        }
    }

    private function initActionMapper()
    {
        if (!isset($this->actionMapper)) {
            $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
        }
    }

    private function initResponsibleMapper()
    {
        if (!isset($this->responsibleMapper)) {
            $this->responsibleMapper = new C3op_Resources_ResponsibleMapper($this->db);
        }
    }

    private function initContactMapper()
    {
        if (!isset($this->contactMapper)) {
            $this->contactMapper = new C3op_Register_ContactMapper($this->db);
        }
    }



    private function initContractMapper()
    {
        if (!isset($this->contractMapper)) {
            $this->contractMapper = new C3op_Projects_ContractMapper($this->db);
        }
    }

    private function initContractWithCheckedId(C3op_Projects_ContractMapper $mapper)
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
        throw new C3op_Finances_OutlayException("Invalid Action Id from Get");

    }

    private function setDateValueToFormField(Zend_Form $form, $fieldName, $value)
    {
        $field = $form->getElement($fieldName);
        $validator = new C3op_Util_ValidDate();
        if ((!is_null($value)) && ($validator->isValid($value))) {
            $field->setValue(C3op_Util_DateDisplay::FormatDateToShow($value));
        } else {
            $field->setValue("");
        }
    }

}