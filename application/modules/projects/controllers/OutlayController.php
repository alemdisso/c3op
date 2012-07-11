<?php

class Projects_OutlayController  extends Zend_Controller_Action
{
    private $actionMapper;
    private $humanResourceMapper;
    private $projectMapper;
    private $outlayMapper;
    private $viewInfo;
    private $db;
    
     public function createAction()
    {
        // cria form
        $form = new C3op_Form_OutlayCreate;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');          
                $this->_redirect('/projects/outlay/success-create/?id=' . $postData['action']);
            } else throw new C3op_Projects_OutlayException("Invalid data for an outlay");
        } else {
            $data = $this->_request->getParams();
            if (isset($data['humanResource'])) {
                $humanResourceId = $data['humanResource'];
                if (!isset($this->humanResourceMapper)) {
                    $this->humanResourceMapper = new C3op_Projects_HumanResourceMapper($this->db);
                }
                $outlayHumanResource = $this->humanResourceMapper->findById($humanResourceId);
                $this->populateFieldsAssociatedToHumanResource($outlayHumanResource, $form);
            } else {
                throw new C3op_Projects_HumanResourceException("Um desembolso precisa estar associado a um recurso.");
                $humanResourceId = 0;
                $projectId = $data['project'];
            }

        }
        $this->view->viewInfo = $this->viewInfo;
    }
    
    public function editAction()
    {
        $form = new C3op_Form_OutlayEdit;
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');          
                $this->_redirect('/projects/outlay/success-create/?id=' . $postData['action']);
            } else throw new C3op_Projects_ProjectException("Invalid data for an outlay.");
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
                if (!isset($this->outlayMapper)) {
                    $this->outlayMapper = new C3op_Projects_OutlayMapper($this->db);
                }
                $thisOutlay = $this->outlayMapper->findById($id);
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $id);
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'project', $thisOutlay->GetProject());
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'action', $thisOutlay->GetAction());
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'humanResource', $thisOutlay->GetHumanResource());
                $this->SetDateValueToFormField($form, 'predictedDate', $thisOutlay->GetPredictedDate());
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'predictedValue', $thisOutlay->GetPredictedValue());
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'observation', $thisOutlay->GetObservation());

            }

        }
    }
    
    private function populateFieldsAssociatedToHumanResource(C3op_Projects_HumanResource $humanResource, C3op_Form_OutlayCreate $form)
    {
        
        $humanResourceField = $form->getElement('humanResource');
        $humanResourceField->setValue($humanResource->Getid());
        
        if ($humanResource->GetContact() > 0) {
            if (!isset($this->contactMapper)) {
                $this->contactMapper = new C3op_Register_ContactMapper($this->db);
            }
            $humanResourceContact = $this->contactMapper->findById($humanResource->GetContact());
            
            $this->viewInfo['contactName'] = $humanResourceContact->GetName();
            $this->viewInfo['linkContactDetail'] = "/register/contact/detail/?id=" . $humanResourceContact->GetId();
        }
        
        if (!isset($this->actionMapper)) {
            $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
        }
        $actionField = $form->getElement('action');
        $actionField->setValue($humanResource->GetAction());
        
        $humanResourceAction = $this->actionMapper->findById($humanResource->GetAction());
        $this->viewInfo['actionTitle'] = $humanResourceAction->GetTitle();
        $this->viewInfo['linkActionDetail'] = "/projects/action/detail/?id=" . $humanResourceAction->GetId();
        
        $projectField = $form->getElement('project');
        $projectField->setValue($humanResourceAction->GetProject());
        
        if (!isset($this->projectMapper)) {
            $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
        }
        $thisProject = $this->projectMapper->findById($humanResourceAction->GetProject());
        $this->viewInfo['projectTitle'] = $thisProject->GetTitle();
        $this->viewInfo['linkProjectDetail'] = "/projects/project/detail/?id=" . $thisProject->GetId();
        
   }
     
    public function successCreateAction()
    {

        $this->initActionMapper();
        $actionRelated =  $this->initActionWithCheckedId($this->actionMapper);
        
        if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();    
            $this->getResponse()->setHeader('Refresh', '3; URL=/projects/action/detail/?id=' . $actionRelated->GetId());
        } else {
            $this->_redirect('/projects');    
        } 
    }

    private function initActionMapper()
    {
        if (!isset($this->actionMapper)) {
            $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
        }        
    }
    
    private function initOutlayMapper()
    {
        if (!isset($this->outlayMapper)) {
            $this->outlayMapper = new C3op_Projects_OutlayMapper($this->db);
        }        
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

