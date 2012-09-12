<?php

class Register_ContactController extends Zend_Controller_Action
{
    private $contactMapper;
    private $db;

    public function preDispatch()
    {
        try {
            $checker = new C3op_Access_PrivilegeChecker();
        } catch (Exception $e) {
            $this->_helper->getHelper('FlashMessenger')
                ->addMessage('Acesso negado');
            $this->_redirect('/register');
        }
    }

    public function init()
    {
        $this->db = Zend_Registry::get('db');
        $this->contactMapper = new C3op_Register_ContactMapper($this->db);
    }

    public function indexAction()
    {
        $list = $this->contactMapper->getAllIds();
        $contactsList = array();
        reset ($list);
        foreach ($list as $id) {
            $thisContact = $this->contactMapper->findById($id);
            $contactsList[$id] = array(
                'name'  => $thisContact->GetName(),
                'type'  => C3op_Register_ContactTypes::TitleForType($thisContact->GetType()),
            );
        }

        $pageData = array(
            'contactsList' => $contactsList,
        );
        $this->view->pageData = $pageData;
    }

    public function createAction()
    {
        // cria form
        $form = new C3op_Form_ContactCreate;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');
                $this->_redirect('/register/contact/success-create/?id=' . $id);
            } else throw new C3op_Register_ContactException("Invalid data");
        }
    }

    public function editAction()
    {
        $form = new C3op_Form_ContactEdit;
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');
                $this->_redirect('/register/contact/success-create/?id=' . $id);
            } else throw new C3op_Register_ContactException("A contact must have a valid name.");
        } else {
            // GET
            $id = $this->checkIdFromGet();
            $contactData = array();
            $contactData["id"] = $id;
            $this->view->contactData = $contactData;
            $thisContact = $this->contactMapper->findById($id);
            $nameField = $form->getElement('name');
            $nameField->setValue($thisContact->getName());
            $idField = $form->getElement('id');
            $idField->setValue($id);
            $typeField = $form->getElement('type');
            $typeField->setValue($thisContact->GetType());
            $pageData = array(
              'contactData' => $contactData,
            );
        }
    }

    public function successCreateAction()
    {
        $this->initContactMapper();
        $contact =  $this->initContactWithCheckedId($this->contactMapper);

        if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();
            $this->getResponse()->setHeader('Refresh', '3; URL=/register/contact/detail/?id=' . $contact->getId());
        } else {
            $this->_redirect('/register/contact');
        }
    }

    public function detailAction()
    {
        $id = $this->checkIdFromGet();
        $contactBeingDetailed = $this->contactMapper->findById($id);

        //  contactInfo
        //    id
        //    name
        //    relationship
        $contactInfo = array(
                'id'           => $id,
                'name'         => $contactBeingDetailed->GetName(),
                'relationship' => C3op_Register_ContactTypes::TitleForType($contactBeingDetailed->GetType())
            );

        //  phonesList
        //   * phoneId =>
        //      area_code
        //      local_number
        //      label
        $objList = $contactBeingDetailed->GetPhoneNumbers();
        $phonesList = array();
        foreach($objList as $messengerId => $phoneNumber) {
            $phonesList[$messengerId] = array(
                'areaCode' => $phoneNumber->GetAreaCode(),
                'localNumber' => $phoneNumber->GetLocalNumber(),
                'label' => $phoneNumber->GetLabel(),
            );
        }

        //  emailsList
        //    * emailId =>
        //      address
        //      label
        $objList = $contactBeingDetailed->GetEmails();
        $emailsList = array();
        foreach($objList as $emailId => $email) {
            $emailsList[$emailId] = array(
                'address' => $email->GetAddress(),
                'label' => $email->GetLabel(),
            );
        }

        //  messengersList
        //    * messengerId =>
        //      address
        //      service
        $objList = $contactBeingDetailed->GetMessengers();
        $messengersList = array();
        foreach($objList as $messengerId => $messenger) {
            $messengersList[$messengerId] = array(
                'address' => $messenger->GetAddress(),
                'service' => $messenger->GetService(),
            );
        }

        //  linkagesList
        //    * linkageId =>
        //      institutionName
        //      department
        //      position
        $linkageMapper = new C3op_Register_LinkageMapper($this->db);
        $objList = $this->contactMapper->getAllLinkages($contactBeingDetailed);
        $linkagesList = array();
        foreach ($objList as $linkageId) {
            $contactLinkage = $linkageMapper->findById($linkageId);
            if ($contactLinkage->GetInstitution() > 0) {
                $institutionMapper = new C3op_Register_InstitutionMapper($this->db);
                $institutionLinkedToContact = $institutionMapper->findById($contactLinkage->GetInstitution());
            }

            $linkagesList[$linkageId] = array(
                'institutionName' => $institutionLinkedToContact->GetName(),
                'department'      => $contactLinkage->GetDepartment(),
                'position'        => $contactLinkage->GetPosition(),
            );
        }

        $pageData = array(
            'contactInfo' => $contactInfo,
            'phonesList' => $phonesList,
            'emailsList' => $emailsList,
            'messengersList' => $messengersList,
            'linkagesList' => $linkagesList,
        );

        $this->view->pageData = $pageData;
    }

    public function addPhoneNumberAction()
    {
        // cria form
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            $validator = new C3op_Util_ValidPositiveInteger;
            if (!$validator->isValid($postData['contact'])) {
                throw new C3op_Register_PhoneNumberException("Invalid contact id");
            }
            $options['contact'] = $postData['contact'];
            $form = new C3op_Form_ContactPhoneNumberCreate($options);
            $this->view->form = $form;

            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');
                $this->_redirect('/register/contact/success-create/?id=' . $id);
            } else throw new C3op_Register_ContactException("Invalid data for phone number.");
        } else {
            $contactId = $this->checkContactFromGet();
            $contactHasPhone = $this->contactMapper->findById($contactId);
            $options['contact'] = $contactId;
            $form = new C3op_Form_ContactPhoneNumberCreate($options);
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'contact', $contactId);

            $this->view->form = $form;
            $pageData = array(
                'id' => $contactId,
                'contactName' => $contactHasPhone->GetName(),
            );
            $this->view->pageData = $pageData;
        }
    }

    public function changePhoneNumberAction()
    {
        // cria form
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            $validator = new C3op_Util_ValidPositiveInteger;
            if (!$validator->isValid($postData['id'])) {
                throw new C3op_Register_PhoneNumberException("Invalid phone number");
            }
            $options['id'] = $postData['id'];
            $form = new C3op_Form_ContactPhoneNumberEdit($options);
            $this->view->form = $form;
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');
                $this->_redirect('/register/contact/success-create/?id=' . $id);
            } else throw new C3op_Register_ContactException("Invalid data for phone number.");
        } else {
            $data = $this->_request->getParams();
            $filters = array(
                'phone' => new Zend_Filter_Alnum(),
            );
            $validators = array(
                'phone' => array('Digits', new Zend_Validate_GreaterThan(0)),
            );
            $input = new Zend_Filter_Input($filters, $validators, $data);
            if ($input->isValid()) {
                $phoneId = $input->phone;
            } else {
                throw new C3op_Register_ContactException("Invalid Contact Id from Get");
            }

            $contactHasPhone = $this->contactMapper->findByPhoneId($phoneId);
            $phoneNumbers = $contactHasPhone->GetPhoneNumbers();
            $phoneNumber = $phoneNumbers[$phoneId];


            $options['id'] = $phoneId;
            $form = new C3op_Form_ContactPhoneNumberEdit($options);
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'contact', $contactHasPhone->GetId());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $phoneId);
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'areaCode', $phoneNumber->GetAreaCode());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'localNumber', $phoneNumber->GetLocalNumber());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'label', $phoneNumber->GetLabel());

            $this->view->form = $form;
            $pageData = array(
                'id' => $contactHasPhone->GetId(),
                'contactName' => $contactHasPhone->GetName(),
            );

            $this->view->pageData = $pageData;
        }
    }

    public function addEmailAction()
    {
        // cria form
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            $validator = new C3op_Util_ValidPositiveInteger;
            if (!$validator->isValid($postData['contact'])) {
                throw new C3op_Register_EmailException("Invalid contact id");
            }
            $options['contact'] = $postData['contact'];
            $form = new C3op_Form_ContactEmailCreate($options);
            $this->view->form = $form;

            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');
                $this->_redirect('/register/contact/success-create/?id=' . $id);
            } else {
                $form->populate($postData);
                $this->view->form = $form;
            }
        } else {
            $contactId = $this->checkContactFromGet();
            $contactHasEmail = $this->contactMapper->findById($contactId);
            $data = $this->_request->getParams();
            $options['contact'] = $contactId;
            $form = new C3op_Form_ContactEmailCreate($options);
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'contact', $contactId);

            $this->view->form = $form;
            $pageData = array(
                'id' => $contactId,
                'contactName' => $contactHasEmail->GetName(),
            );

            $this->view->pageData = $pageData;
        }
    }

    public function changeEmailAction()
    {
        // cria form
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            $options['id'] = $postData['id'];
            $form = new C3op_Form_ContactEmailEdit($options);
            $this->view->form = $form;
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');
                $this->_redirect('/register/contact/success-create/?id=' . $id);
            } else throw new C3op_Register_ContactException("Invalid data for email.");
        } else {
            $data = $this->_request->getParams();

            $filters = array(
                'email' => new Zend_Filter_Alnum(),
            );
            $validators = array(
                'email' => array('Digits', new Zend_Validate_GreaterThan(0)),
            );
            $input = new Zend_Filter_Input($filters, $validators, $data);
            if ($input->isValid()) {
                $emailId = $input->email;
            } else {
                throw new C3op_Register_ContactException("Invalid Email Id from Get");
            }

            $contactHasEmail = $this->contactMapper->findByEmailId($emailId);
            $emails = $contactHasEmail->GetEmails();
            $email = $emails[$emailId];

            $options['id'] = $emailId;
            $form = new C3op_Form_ContactEmailEdit($options);
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'contact', $contactHasEmail->GetId());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $emailId);
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'address', $email->GetAddress());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'label', $email->GetLabel());

            $this->view->form = $form;
            $pageData = array(
                'id' => $contactHasMessenger->GetId(),
                'contactName' => $contactHasMessenger->GetName(),
            );

            $this->view->pageData = $pageData;
        }
    }

    public function addMessengerAction()
    {
        // cria form
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            $validator = new C3op_Util_ValidPositiveInteger;
            if (!$validator->isValid($postData['contact'])) {
                throw new C3op_Register_MessengerException("Invalid contact id");
            }
            $options['contact'] = $postData['contact'];
            $form = new C3op_Form_ContactMessengerCreate($options);
            $this->view->form = $form;

            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');
                $this->_redirect('/register/contact/success-create/?id=' . $id);
            } else {
                $form->populate($postData);
                $this->view->form = $form;
            }
        } else {
            $contactId = $this->checkContactFromGet();
            $contactHasMessenger = $this->contactMapper->findById($contactId);
            $data = $this->_request->getParams();
            $options['contact'] = $contactId;
            $form = new C3op_Form_ContactMessengerCreate($options);
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'contact', $contactId);

            $this->view->form = $form;
            $pageData = array(
                'id' => $contactId,
                'contactName' => $contactHasMessenger->GetName(),
            );

            $this->view->pageData = $pageData;
        }
    }

    public function changeMessengerAction()
    {
        // cria form
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            $options['id'] = $postData['id'];
            $form = new C3op_Form_ContactMessengerEdit($options);
            $this->view->form = $form;
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');
                $this->_redirect('/register/contact/success-create/?id=' . $id);
            } else throw new C3op_Register_ContactException("Invalid data for messenger.");
        } else {
            $data = $this->_request->getParams();

            $filters = array(
                'messenger' => new Zend_Filter_Alnum(),
            );
            $validators = array(
                'messenger' => array('Digits', new Zend_Validate_GreaterThan(0)),
            );
            $input = new Zend_Filter_Input($filters, $validators, $data);
            if ($input->isValid()) {
                $messengerId = $input->messenger;
            } else {
                throw new C3op_Register_ContactException("Invalid Messenger Id from Get");
            }

            $contactHasMessenger = $this->contactMapper->findByMessengerId($messengerId);
            $messengers = $contactHasMessenger->GetMessengers();
            $messenger = $messengers[$messengerId];

            $options['id'] = $messengerId;
            $form = new C3op_Form_ContactMessengerEdit($options);
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'contact', $contactHasMessenger->GetId());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $messengerId);
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'address', $messenger->GetAddress());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'service', $messenger->GetService());

            $this->view->form = $form;
            $pageData = array(
                'id' => $contactHasMessenger->GetId(),
                'contactName' => $contactHasMessenger->GetName(),
            );

            $this->view->pageData = $pageData;
        }
    }

    private function initContactMapper()
    {
        if (!isset($this->contactMapper)) {
            $this->contactMapper = new C3op_Register_ContactMapper($this->db);
        }
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
        throw new C3op_Register_ContactException("Invalid Contact Id from Get");

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
        throw new C3op_Register_ContactException("Invalid Contact Id from Get");

    }


    private function initContactWithCheckedId(C3op_Register_ContactMapper $mapper)
    {
        return $mapper->findById($this->checkIdFromGet());
    }

    private function SetValueToFormField(Zend_Form $form, $fieldName, $value)
    {
        $field = $form->getElement($fieldName);
        $field->setValue($value);
    }


}