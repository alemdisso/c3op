<?php

class Register_ContactController extends Zend_Controller_Action
{
    private $contactMapper;
    private $db;

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
                $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');          
                $this->_redirect('/register/contact/success-create');

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
                $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');          
                $this->_redirect('/register/contact/success-create');
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
        if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();    
            $this->getResponse()->setHeader('Refresh', '3; URL=/register/contact');
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
        $thisContact = $this->contactMapper->findById($id);

        $linkagesIdsList = $this->contactMapper->getAllLinkages($thisContact);
        $linkagesList = array();
        reset ($linkagesList);
        foreach ($linkagesIdsList as $linkageId) {
            $thisLinkage = $linkageMapper->findById($linkageId);
            
            if ($thisLinkage->GetInstitution() > 0) {
                $institutionMapper = new C3op_Register_InstitutionMapper($this->db);
                $thisInstitution = $institutionMapper->findById($thisLinkage->GetInstitution());
            }
            
            $linkagesList[$linkageId] = array(
                'institutionName' => $thisInstitution->GetName(),
                'institutionEdit' => '/register/institution/edit/?id=' . $thisInstitution->GetId(),
                'department' => $thisLinkage->GetDepartment(),
                'position' => $thisLinkage->GetPosition(),
                'editLink' => '/register/linkage/edit/?id=' . $linkageId   ,
            );
        }
        $contactInfo = array(
            'name' => $thisContact->GetName(),
            'editLink' => '/register/contact/edit/?id=' . $id   ,
            'linkLinkageCreate' => '/register/linkage/create/?contact=' . $id   ,
            'linkagesList' => $linkagesList,
        );

        $this->view->contactInfo = $contactInfo;
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

    public function receivablesAction()
    {
        $actionMapper = new C3op_Register_ActionMapper($this->db);

        $id = $this->checkIdFromGet();
        $thisContact = $this->contactMapper->findById($id);
        $productsIdList = $this->contactMapper->getAllProducts($thisContact);
        $productsList = array();
        reset ($productsList);
        foreach ($productsIdList as $actionId) {
            $thisAction = $actionMapper->findById($actionId);

            $productsList[$actionId] = array(
                'name' => $thisAction->GetName(),
                'editLink' => '/register/action/edit/?id=' . $actionId   ,
            );
        }
        $contactInfo = array(
            'name' => $thisContact->GetName(),
            'editLink' => '/register/contact/edit/?id=' . $id   ,
            'productsList' => $productsList,
        );

        $this->view->contactInfo = $contactInfo;
    }
}