<?php

class Register_InstitutionController extends Zend_Controller_Action
{
    private $institutionMapper;
    private $db;

    public function init()
    {
        $this->db = Zend_Registry::get('db');
        $this->institutionMapper = new C3op_Register_InstitutionMapper($this->db);
    }
    
    public function indexAction()
    {

        $list = $this->institutionMapper->getAllIds();
        $institutionsList = array();
        reset ($list);
        foreach ($list as $id) {
            $thisInstitution = $this->institutionMapper->findById($id);
            
            $institutionsList[$id] = array(
                'name' => $thisInstitution->GetShortName(),
                'editLink' => '/register/institution/edit/?id=' . $id   ,
                'type' => C3op_Register_InstitutionTypes::TitleForType($thisInstitution->GetType()),
            );
        }
        
        $this->view->institutionsList = $institutionsList;
        
        $this->view->createInstitutionLink = "/register/institution/create";
        
 
    }

    public function createAction()
    {
        // cria form
        $form = new C3op_Form_InstitutionCreate;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            try {
                if ($form->isValid($postData)) {
                    $form->process($postData);
                    $this->_helper->getHelper('FlashMessenger')
                        ->addMessage('The record was successfully updated.');          
                    $this->_redirect('/register/institution/success-create');
                }
            } catch (Exception $e) {
                $this->view->form = $form;
                return $this->render('create');
            }
        }
    }

    public function editAction()
    {
        $form = new C3op_Form_InstitutionEdit;
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            try {
                if ($form->isValid($postData)) {
                    $form->process($postData);
                    $this->_helper->getHelper('FlashMessenger')
                        ->addMessage('The record was successfully updated.');          
                    $this->_redirect('/register/institution/success-create');

                } 
//                else throw new C3op_Register_InstitutionException("Invalid data for institution.");
            } catch (Exception $e) {
                throw $e;
                $this->view->form = $form;
                return $this->render('edit');
            }
        } else {
            // GET
            $id = $this->checkIdFromGet();
            $thisInstitution = $this->institutionMapper->findById($id);
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'name', $thisInstitution->getName());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $id);
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'shortName', $thisInstitution->getShortName());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'legalEntity', $thisInstitution->getLegalEntity());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'registerNumber', $thisInstitution->getRegisterNumber());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'stateRegistration', $thisInstitution->getStateRegistration());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'localRegisterNumber', $thisInstitution->getLocalRegisterNumber());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'street', $thisInstitution->getStreet());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'streetNumber', $thisInstitution->getStreetNumber());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'addressComplement', $thisInstitution->getAddressComplement());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'district', $thisInstitution->getDistrict());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'zipCode', $thisInstitution->getZipCode());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'city', $thisInstitution->getCity());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'state', $thisInstitution->getState());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'website', $thisInstitution->getWebsite());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'type', $thisInstitution->getType());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'relationshipType', $thisInstitution->getRelationshipType());

        }
    }

    public function sucessAction()
    {
        if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $this->view->messages = $this->_helper
                ->getHelper('FlashMessenger')
                ->getMessages();
        } else {
            $this->_redirect('/register/institution');
        }
    }

    public function successCreateAction()
    {
        if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();    
            $this->getResponse()->setHeader('Refresh', '3; URL=/register/institution');
        } else {
            $this->_redirect('/register/institution');    
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
        $contactMapper = new C3op_Register_ContactMapper($this->db);

        $id = $this->checkIdFromGet();
        $thisInstitution = $this->institutionMapper->findById($id);

        $linkagesIdsList = $this->institutionMapper->getAllLinkages($thisInstitution);
        $linkagesList = array();
        reset ($linkagesList);
        foreach ($linkagesIdsList as $linkageId) {
            $thisLinkage = $linkageMapper->findById($linkageId);
            $thisContact = $contactMapper->findById($thisLinkage->GetContact());
            
            $linkagesList[$linkageId] = array(
                'name' => $thisContact->GetName(),
                'position' => $thisLinkage->GetPosition(),
                'department' => $thisLinkage->GetDepartment(),
                'editLink' => '/register/contact/edit/?id=' . $linkageId   ,
            );
        }
        $institutionInfo = array(
            'name' => $thisInstitution->GetName(),
            'editLink' => '/register/institution/edit/?id=' . $id   ,
            'linkLinkageCreate' => '/register/linkage/create/?institution=' . $id,
            'contactsList' => $linkagesList,
        );

        $this->view->institutionInfo = $institutionInfo;
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
        throw new C3op_Register_InstitutionException("Invalid Institution Id from Get");

    }


}