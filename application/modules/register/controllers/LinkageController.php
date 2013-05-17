<?php

class Register_LinkageController extends Zend_Controller_Action
{
    private $linkageMapper;
    private $db;

    public function preDispatch()
    {
        try {
            $checker = new C3op_Access_PrivilegeChecker();
        } catch (Exception $e) {
            $this->_helper->getHelper('FlashMessenger')
                ->addMessage(_('#Access denied'));
            $this->_redirect('/register');
        }
    }

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

                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully created.'));
                $this->_redirect('/register/contact/detail/?success=1&id=' . $postData['contact']);
            } else {
                //form error: populate and go back
                $form->populate($postData);
                $this->view->form = $form;
            }
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
            if ($form->isValid($postData)) {
                $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/register/contact/detail/?id=' . $postData['contact']);
            } else {
                //form error: populate and go back
                $form->populate($postData);
                $this->view->form = $form;
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
            $this->populateInstitutionsField($form, $thisLinkage->GetInstitution());
            $this->view->form = $form;
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $id);
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'department', $thisLinkage->getDepartment());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'position', $thisLinkage->getPosition());
        }
    }

    public function detailAction()
    {

        $id = $this->checkIdFromGet();
        $linkageBeingDetailed = $this->linkageMapper->findById($id);

        $phoneNumbersList = $linkageBeingDetailed->GetPhoneNumbers();
        $phoneData = array();
        foreach($phoneNumbersList as $phoneId => $phoneNumber) {
            $phoneData[$phoneNumber->GetId()] = array(
                'area_code' => $phoneNumber->GetAreaCode(),
                'local_number' => $phoneNumber->GetLocalNumber(),
                'label' => $phoneNumber->GetLabel(),
            );
        }
        if ($linkageBeingDetailed->GetInstitution() > 0) {
            $institutionMapper = new C3op_Register_InstitutionMapper($this->db);
            $institutionLinkedToContact = $institutionMapper->findById($linkageBeingDetailed->GetInstitution());
        }

        $emailsList = $linkageBeingDetailed->GetEmails();
        $emailData = array();
        foreach($emailsList as $emailId => $email) {
            $emailData[$email->GetId()] = array(
                'email' => $email->GetAddress(),
                'label' => $email->GetLabel(),
            );
        }

        $linkageInfo = array(
            'id'              => $id,
            'institutionName' => $institutionLinkedToContact->GetName(),
            'department'      => $linkageBeingDetailed->GetDepartment(),
            'position'        => $linkageBeingDetailed->GetPosition(),
            'phoneData'     => $phoneData,
            'emailData'     => $emailData,
        );

        $this->view->linkageInfo = $linkageInfo;
    }

    public function addPhoneNumberAction()
    {
        // cria form
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            $options['linkage'] = $postData['linkage'];
            $form = new C3op_Form_LinkagePhoneNumberCreate($options);
            $this->view->form = $form;

            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/register/linkage/detail/?success=1&id=' . $id);
            } else {
                //form error: populate and go back
                $form->populate($postData);
                $this->view->form = $form;
            }
        } else {
            $linkageId = $this->checkLinkageFromGet();
            $linkageHasPhone = $this->linkageMapper->findById($linkageId);
            $data = $this->_request->getParams();
            $options['linkage'] = $linkageId;
            $form = new C3op_Form_LinkagePhoneNumberCreate($options);
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'linkage', $linkageId);
            if ($linkageHasPhone->GetInstitution() > 0) {
                $institutionMapper = new C3op_Register_InstitutionMapper($this->db);
                $institutionLinkedToContact = $institutionMapper->findById($linkageHasPhone->GetInstitution());
            }

            if ($linkageHasPhone->GetContact() > 0) {
                $contactMapper = new C3op_Register_ContactMapper($this->db);
                $contactLinkedToInstitution = $contactMapper->findById($linkageHasPhone->GetContact());
            }

            $this->view->form = $form;
            $linkageInfo = array(
                'id' => $linkageId,
                'name' => $contactLinkedToInstitution->GetName(),
                'institutionName' => $institutionLinkedToContact->GetName(),
            );

            $this->view->linkageInfo = $linkageInfo;
        }
    }

    public function changePhoneNumberAction()
    {
        // cria form
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            $options['id'] = $postData['id'];
            $form = new C3op_Form_LinkagePhoneNumberEdit($options);
            $this->view->form = $form;
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/register/linkage/detail/?success=1&id=' . $id);
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
                'id' => array('Digits', new Zend_Validate_GreaterThan(0)),
            );
            $input = new Zend_Filter_Input($filters, $validators, $data);
            if ($input->isValid()) {
                $phoneId = $input->id;
            } else {
                throw new C3op_Register_LinkageException(_("#Invalid Linkage Id from Get"));
            }

            $linkageHasPhone = $this->linkageMapper->findByPhoneId($phoneId);
            $phoneNumbers = $linkageHasPhone->GetPhoneNumbers();
            $phoneNumber = $phoneNumbers[$phoneId];

            $data = $this->_request->getParams();

            $options['id'] = $data['id'];
            $form = new C3op_Form_LinkagePhoneNumberEdit($options);
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'linkage', $linkageHasPhone->GetId());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $phoneId);
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'areaCode', $phoneNumber->GetAreaCode());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'localNumber', $phoneNumber->GetLocalNumber());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'label', $phoneNumber->GetLabel());

            $this->view->form = $form;

            if ($linkageHasPhone->GetInstitution() > 0) {
                $institutionMapper = new C3op_Register_InstitutionMapper($this->db);
                $institutionLinkedToContact = $institutionMapper->findById($linkageHasPhone->GetInstitution());
            }

            if ($linkageHasPhone->GetContact() > 0) {
                $contactMapper = new C3op_Register_ContactMapper($this->db);
                $contactLinkedToInstitution = $contactMapper->findById($linkageHasPhone->GetContact());
            }

            $this->view->form = $form;
            $linkageInfo = array(
                'id' => $linkageHasPhone->GetId(),
                'name' => $contactLinkedToInstitution->GetName(),
                'institutionName' => $institutionLinkedToContact->GetName(),
            );

            $this->view->linkageInfo = $linkageInfo;
        }
    }

    public function addEmailAction()
    {
        // cria form
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            $options['linkage'] = $postData['linkage'];
            $form = new C3op_Form_LinkageEmailCreate($options);
            $this->view->form = $form;

            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/register/linkage/detail/?success=1&id=' . $id);
            } else throw new C3op_Register_LinkageException("Invalid data for email.");
        } else {
            $linkageId = $this->checkLinkageFromGet();
            $linkageHasEmail = $this->linkageMapper->findById($linkageId);
            $data = $this->_request->getParams();
            $options['linkage'] = $linkageId;
            $form = new C3op_Form_LinkageEmailCreate($options);
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'linkage', $linkageId);

            if ($linkageHasEmail->GetInstitution() > 0) {
                $institutionMapper = new C3op_Register_InstitutionMapper($this->db);
                $institutionLinkedToContact = $institutionMapper->findById($linkageHasEmail->GetInstitution());
            }
            if ($linkageHasEmail->GetContact() > 0) {
                $contactMapper = new C3op_Register_ContactMapper($this->db);
                $contactLinkedToInstitution = $contactMapper->findById($linkageHasEmail->GetContact());
            }

            $this->view->form = $form;
            $linkageInfo = array(
                'id' => $linkageId,
                'institutionName' => $institutionLinkedToContact->GetName(),
                'contactName' => $contactLinkedToInstitution->GetName(),
            );

            $this->view->linkageInfo = $linkageInfo;
        }
    }

    public function changeEmailAction()
    {
        // cria form
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            $options['id'] = $postData['id'];
            $form = new C3op_Form_LinkageEmailEdit($options);
            $this->view->form = $form;
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/register/linkage/detail/?success=1&id=' . $id);
            } else throw new C3op_Register_LinkageException("Invalid data for email.");
        } else {
            $data = $this->_request->getParams();
            $filters = array(
                'id' => new Zend_Filter_Alnum(),
            );
            $validators = array(
                'id' => array('Digits', new Zend_Validate_GreaterThan(0)),
            );
            $input = new Zend_Filter_Input($filters, $validators, $data);
            if ($input->isValid()) {
                $emailId = $input->id;
            } else {
                throw new C3op_Register_LinkageException(_("#Invalid Linkage Id from Get"));
            }

            $linkageHasEmail = $this->linkageMapper->findByEmailId($emailId);
            $emails = $linkageHasEmail->GetEmails();
            $email = $emails[$emailId];

            $data = $this->_request->getParams();

            $options['id'] = $data['id'];
            $form = new C3op_Form_LinkageEmailEdit($options);
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'linkage', $linkageHasEmail->GetId());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $emailId);
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'email', $email->GetAddress());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'label', $email->GetLabel());

            $this->view->form = $form;
            if ($linkageHasEmail->GetInstitution() > 0) {
                $institutionMapper = new C3op_Register_InstitutionMapper($this->db);
                $institutionLinkedToContact = $institutionMapper->findById($linkageHasEmail->GetInstitution());
            }
            if ($linkageHasEmail->getContact_deprecated() > 0) {
                $contactMapper = new C3op_Register_ContactMapper($this->db);
                $contactLinkedToInstitution = $contactMapper->findById($linkageHasEmail->getContact_deprecated());
            }

            $this->view->form = $form;
            $linkageInfo = array(
                'id' => $linkageId,
                'institutionName' => $institutionLinkedToContact->GetName(),
                'contactName' => $contactLinkedToInstitution->GetName(),
            );

            $this->view->linkageInfo = $linkageInfo;
        }
    }

   public function removeAction()
    {
        // cria form
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            $linkageMapper = new C3op_Register_LinkageMapper($db);
            $linkageToBeRemoved = $linkageMapper->FindById($postData['id']);
            $contactId = $linkageToBeRemoved->getContact();


            $form = new C3op_Form_LinkageRemove();
            $this->view->form = $form;
            if ($form->isValid($postData)) {
                $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully removed.'));
                $this->_redirect('/register/contact/detail/?id='.$contactId);
            } else {
                //form error: populate and go back
                $form->populate($postData);
                $this->view->form = $form;
            }
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
            $this->view->removalMessage = sprintf(_("#Confirm removal of linkage between %s and %s?"), $thisContact->GetName(), $thisInstitution->GetName());
            $this->view->form = $form;
        }
    }

    private function initLinkageMapper()
    {
        if (!isset($this->linkageMapper)) {
            $this->linkageMapper = new C3op_Register_LinkageMapper($this->db);
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

    private function populateInstitutionsField(C3op_Form_LinkageCreate $form, $currentInstitution=0)
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
            $this->view->institutionName = $linkageInstitution->GetShortName();
            $this->view->linkInstitutionDetail = "/register/institution/detail/?id=" . $currentInstitution;
        }

        $allInstitutions = $this->institutionMapper->getAllIdsOrderedByShortName();
        while (list($key, $institutionId) = each($allInstitutions)) {
            $eachInstitution = $this->institutionMapper->findById($institutionId);
            $institutionField->addMultiOption($institutionId, $eachInstitution->GetShortName());
        }
        $institutionField->setValue($currentInstitution);
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
        throw new C3op_Register_LinkageException(_("#Invalid Linkage Id from Get"));

    }

   private function checkLinkageFromGet()
    {
        $data = $this->_request->getParams();
        $filters = array(
            'linkage' => new Zend_Filter_Alnum(),
        );
        $validators = array(
            'linkage' => array('Digits', new Zend_Validate_GreaterThan(0)),
        );
        $input = new Zend_Filter_Input($filters, $validators, $data);
        if ($input->isValid()) {
            $linkage = $input->linkage;
            return $linkage;
        }
        throw new C3op_Register_LinkageException(_("#Invalid Linkage Id from Get"));
    }

    private function initLinkageWithCheckedId(C3op_Register_LinkageMapper $mapper)
    {
        return $mapper->findById($this->checkIdFromGet());
    }

    private function initContactWithCheckedId(C3op_Register_ContactMapper $mapper)
    {
        return $mapper->findById($this->checkContactFromGet());
    }

   private function checkContactFromGet()
    {
        $data = $this->_request->getParams();
        $filters = array(
            'contact' => new Zend_Filter_Alnum(),
        );
        $validators = array(
            'contact' => array('Digits', new Zend_Validate_GreaterThan(0)),
        );
        $input = new Zend_Filter_Input($filters, $validators, $data);
        if ($input->isValid()) {
            $contact = $input->contact;
            return $contact;
        }
        throw new C3op_Register_LinkageException(_("#Invalid Contact Id from Get"));
    }

}