<?php

class Register_LinkageController extends Zend_Controller_Action
{
    private $linkageMapper;
    private $db;

    public function init()
    {
        $this->db = Zend_Registry::get('db');
        $this->linkageMapper = new C3op_Register_LinkageMapper($this->db);
    }
    
    public function createAction()
    {
        // cria form
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            $options=array();
            $options['contact'] = $postData['contact'];
            $form = new C3op_Form_LinkageCreate($options);
            $this->view->form = $form;
            if ($form->isValid($postData)) {

                $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');          
                $this->_redirect('/register/linkage/success-create');
            } else throw new C3op_Register_LinkageException("An linkage must have valid data.");
        } else {
            $data = $this->_request->getParams();
            $contactId = $data['contact'];
            $options=array();
            $options['contact'] = $data['contact'];
            $form = new C3op_Form_LinkageCreate($options);
            $this->view->form = $form;
            $this->PopulateContactFields($contactId, $form);
            $this->PopulateInstitutionsField($form, 0);
        }
    }

    public function editAction()
    {
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            $options=array();
            $options['contact'] = $postData['contact'];
            $form = new C3op_Form_LinkageEdit($options);
            $this->view->form = $form;
            try {
                if ($form->isValid($postData)) {
                    $form->process($postData);
                    $this->_helper->getHelper('FlashMessenger')
                        ->addMessage('The record was successfully updated.');          
                    $this->_redirect('/register/linkage/success-create');

                } 
//                else throw new C3op_Register_LinkageException("Invalid data for linkage.");
            } catch (Exception $e) {
                throw $e;
                $this->view->form = $form;
                return $this->render('edit');
            }
        } else {
            // GET
            $id = $this->checkIdFromGet();
            $thisLinkage = $this->linkageMapper->findById($id);
            $contactId = $thisLinkage->GetContact();
            $options=array();
            $options['contact'] = $contactId;
            $form = new C3op_Form_LinkageEdit($options);
            $this->PopulateContactFields($contactId, $form);
            $this->PopulateInstitutionsField($form, $thisLinkage->GetInstitution());
            $this->view->form = $form;
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $id);
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'department', $thisLinkage->getDepartment());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'position', $thisLinkage->getPosition());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'state', $thisLinkage->getState());

        }
    }

    public function sucessAction()
    {
        if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $this->view->messages = $this->_helper
                ->getHelper('FlashMessenger')
                ->getMessages();
        } else {
            $this->_redirect('/register/linkage');
        }
    }

    public function successCreateAction()
    {
        if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();    
            $this->getResponse()->setHeader('Refresh', '3; URL=/register');
        } else {
            $this->_redirect('/register');    
        } 
    }

    public function successRemoveAction()
    {
        if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();    
            $this->getResponse()->setHeader('Refresh', '3; URL=/register');
        } else {
            $this->_redirect('/register');    
        } 
    }

    public function errorEditAction()
    {
        $flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $flashMessenger->setNamespace('messages');
        $this->view->messages = $flashMessenger->getMessages();
        $flashMessenger->addMessage('Id Inválido');
    }

    public function detailAction()
    {
        $actionMapper = new C3op_Register_ActionMapper($this->db);

        $id = $this->checkIdFromGet();
        $thisLinkage = $this->linkageMapper->findById($id);
        $productsIdList = $this->linkageMapper->getAllProducts($thisLinkage);
        if (count($productsIdList) > 0) {
            $linkReceivables = '/register/linkage/receivables/?id=' . $thisLinkage->GetId();
        } else {
            $linkReceivables = "";
        }

        $actionsIdsList = $this->linkageMapper->getAllActions($thisLinkage);
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
                'name' => $thisAction->GetName(),
                'milestone' => $milestone,
                'requirementForReceiving' => $requirementForReceiving,
                'editLink' => '/register/action/edit/?id=' . $actionId   ,
            );
        }
        $linkageInfo = array(
            'name' => $thisLinkage->GetName(),
            'editLink' => '/register/linkage/edit/?id=' . $id   ,
            'linkReceivables' => $linkReceivables,
            'beginDate' => $thisLinkage->GetBeginDate(),
            'value' => $thisLinkage->GetValue(),
            'linkActionCreate' => '/register/action/create/?linkage=' . $id,
            'actionsList' => $actionsList,
        );

        $this->view->linkageInfo = $linkageInfo;
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
        throw new C3op_Register_LinkageException("Invalid Linkage Id from Get");

    }
    
    public function removeAction()
    {
        // cria form
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            $form = new C3op_Form_LinkageRemove();
            $this->view->form = $form;
            if ($form->isValid($postData)) {
                $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully removed.');          
                $this->_redirect('/register/linkage/success-remove');
            } else throw new C3op_Register_LinkageException("An linkage must have valid data.");
        } else {
            $data = $this->_request->getParams();
            $form = new C3op_Form_LinkageRemove();
            $id = $this->checkIdFromGet();
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $id);
            $thisLinkage = $this->linkageMapper->findById($id);
            $contactId = $thisLinkage->GetContact();
            $institutionId = $thisLinkage->GetInstitution();
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $id);
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'institution', $institutionId);
            if (!isset($this->institutionMapper)) {
                $this->institutionMapper = new C3op_Register_InstitutionMapper($this->db);
            }
            $thisInstitution = $this->institutionMapper->findById($institutionId);
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'contact', $contactId);
            if (!isset($this->contactMapper)) {
                $this->contactMapper = new C3op_Register_ContactMapper($this->db);
            }
            $thisContact = $this->contactMapper->findById($contactId);
            $this->view->removalMessage = sprintf("Confirma a remoção do vínculo entre %s e %s?", $thisContact->GetName(), $thisInstitution->GetName());
            $this->view->form = $form;
        }
    }

   private function SetValueToFormField(Zend_Form $form, $fieldName, $value)
    {
        $field = $form->getElement($fieldName);
        $field->setValue($value);
    }

    private function PopulateContactFields($contactId, C3op_Form_LinkageCreate $form)
    {
        $validator = new C3op_Util_ValidId();
        if ($validator->isValid($contactId)) {
            $contactField = $form->getElement('contact');
            $contactField->setValue($contactId);
            if (!isset($this->contactMapper)) {
                $this->contactMapper = new C3op_Register_ContactMapper($this->db);
            }
            $thisContact = $this->contactMapper->findById($contactId);
            $this->view->contactName = $thisContact->GetName();
            $this->view->linkContactDetail = "/register/contact/detail/?id=" . $thisContact->GetId();
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'contact', $contactId);

            return $contactId;
        } else throw new C3op_Register_LinkageException("Linkage needs a positive integer contact id.");
    }

    private function PopulateInstitutionsField(C3op_Form_LinkageCreate $form, $currentInstitution=0)
    {
        $institutionField = $form->getElement('institution');
        if (!isset($this->linkageMapper)) {
            $this->linkageMapper = new C3op_Register_LinkageMapper($this->db);
        }

        if (!isset($this->institutionMapper)) {
            $this->institutionMapper = new C3op_Register_InstitutionMapper($this->db);
        }
        if ($currentInstitution > 0) {
            $linkageInstitution = $this->institutionMapper->findById($currentInstitution);
            $this->view->institutionName = $linkageInstitution->GetName();
            $this->view->linkInstitutionDetail = "/register/institution/detail/?id=" . $currentInstitution;
        }
        
        $allInstitutions = $this->institutionMapper->getAllIds();
        while (list($key, $institutionId) = each($allInstitutions)) {
            $eachInstitution = $this->institutionMapper->findById($institutionId);
            $institutionField->addMultiOption($institutionId, $eachInstitution->GetName());
        }
        $institutionField->setValue($currentInstitution);
   }
     
}