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
                'name' => $thisContact->GetName(),
                'editLink' => '/register/contact/edit/?id=' . $id   ,
                'linkDetail' => '/register/contact/detail/?id=' . $id   ,
                'type' => C3op_Register_ContactTypes::TitleForType($thisContact->GetType()),
            );
        }

        $this->view->contactsList = $contactsList;

        $this->view->createContactLink = "/register/contact/create";


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
        $this->initContactMapper();
        $contact =  $this->initContactWithCheckedId($this->contactMapper);

        if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();
            $this->getResponse()->setHeader('Refresh', '3; URL=/register/contact/detail/?id=' . $contact->getId());
        } else {
            $this->_redirect('/register/contact');
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
        $linkageMapper = new C3op_Register_LinkageMapper($this->db);

        $id = $this->checkIdFromGet();
        $contactBeingDetailed = $this->contactMapper->findById($id);

        $phoneNumbersList = $contactBeingDetailed->GetPhoneNumbers();
        $phoneData = array();
        foreach($phoneNumbersList as $phoneId => $phoneNumber) {
            $phoneData[$phoneNumber->GetId()] = array(
                'area_code' => $phoneNumber->GetAreaCode(),
                'local_number' => $phoneNumber->GetLocalNumber(),
                'label' => $phoneNumber->GetLabel(),
            );
        }


        $linkagesIdsList = $this->contactMapper->getAllLinkages($contactBeingDetailed);
        $linkagesList = array();

        reset ($linkagesList);
        foreach ($linkagesIdsList as $linkageId) {
            $contactLinkage = $linkageMapper->findById($linkageId);

            if ($contactLinkage->GetInstitution() > 0) {
                $institutionMapper = new C3op_Register_InstitutionMapper($this->db);
                $institutionLinkedToContact = $institutionMapper->findById($contactLinkage->GetInstitution());
            }

            $linkagesList[$linkageId] = array(
                'id'              => $linkageId,
                'institutionName' => $institutionLinkedToContact->GetName(),
                'institutionEdit' => '/register/institution/edit/?id=' . $institutionLinkedToContact->GetId(),
                'department'      => $contactLinkage->GetDepartment(),
                'position'        => $contactLinkage->GetPosition(),

            );
        }
        $contactInfo = array(
            'id' => $id,
            'name' => $contactBeingDetailed->GetName(),
            'editLink' => '/register/contact/edit/?id=' . $id   ,
            'linkLinkageCreate' => '/register/linkage/create/?contact=' . $id   ,
            'phoneData' => $phoneData,
            'linkagesList' => $linkagesList,
        );

        $this->view->contactInfo = $contactInfo;
    }

    public function addPhoneNumberAction()
    {
        // cria form
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            $form = new C3op_Form_PhoneNumberCreate();
            $this->view->form = $form;
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');
                $this->_redirect('/register/contact/success-create/?id=' . $id);
            } else throw new C3op_Register_ContactException("Invalid data for phone number.");
        } else {
            $contactId = $this->checkIdFromGet();
            $contactHasPhone = $this->contactMapper->findById($contactId);
            $data = $this->_request->getParams();
            $form = new C3op_Form_PhoneNumberCreate();
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'contact', $contactId);

            $this->view->form = $form;
            $contactInfo = array(
                'id' => $contactId,
                'name' => $contactHasPhone->GetName(),
            );

            $this->view->contactInfo = $contactInfo;
        }
    }

    public function changePhoneNumberAction()
    {
        // cria form
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            $form = new C3op_Form_PhoneNumberEdit();
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
                'id' => new Zend_Filter_Alnum(),
            );
            $validators = array(
                'id' => array('Digits', new Zend_Validate_GreaterThan(0)),
            );
            $input = new Zend_Filter_Input($filters, $validators, $data);
            if ($input->isValid()) {
                $phoneId = $input->id;
            } else {
                throw new C3op_Register_ContactException("Invalid Contact Id from Get");
            }

            $contactHasPhone = $this->contactMapper->findByPhoneId($phoneId);
            $phoneNumbers = $contactHasPhone->GetPhoneNumbers();
            $phoneNumber = $phoneNumbers[$phoneId];

            $data = $this->_request->getParams();

            $form = new C3op_Form_PhoneNumberEdit();
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'contact', $contactHasPhone->GetId());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $phoneId);
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'areaCode', $phoneNumber->GetAreaCode());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'localNumber', $phoneNumber->GetLocalNumber());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'label', $phoneNumber->GetLabel());

            $this->view->form = $form;
            $contactInfo = array(
                'id' => $contactHasPhone->GetId(),
                'name' => $contactHasPhone->GetName(),
            );

            $this->view->contactInfo = $contactInfo;
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