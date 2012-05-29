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
                'linkEdit' => '/register/institution/edit/?id=' . $id   ,
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
            if ($form->isValid($postData)) {
                $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');          
                $this->_redirect('/register/institution/success-create');

            } else throw new C3op_Register_InstitutionException("A institution must have a valid name.");
        }
    }

    public function editAction()
    {
        $form = new C3op_Form_InstitutionEdit;
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');          
                $this->_redirect('/register/institution/success-create');
            } else throw new C3op_Register_InstitutionException("A institution must have a valid name.");
        } else {
            // GET
            $id = $this->checkIdFromGet();
            $thisInstitution = $this->institutionMapper->findById($id);
            $this->SetValueToFormField($form, 'name', $thisInstitution->getName());
            $this->SetValueToFormField($form, 'id', $id);
            $this->SetValueToFormField($form, 'shortName', $thisInstitution->getShortName());
            $this->SetValueToFormField($form, 'legalEntity', $thisInstitution->getLegalEntity());
            $this->SetValueToFormField($form, 'registerNumber', $thisInstitution->getRegisterNumber());
            $this->SetValueToFormField($form, 'stateRegistration', $thisInstitution->getStateRegistration());
            $this->SetValueToFormField($form, 'localRegisterNumber', $thisInstitution->getLocalRegisterNumber());
            $this->SetValueToFormField($form, 'street', $thisInstitution->getStreet());
            $this->SetValueToFormField($form, 'streetNumber', $thisInstitution->getStreetNumber());
            $this->SetValueToFormField($form, 'addressComplement', $thisInstitution->getAddressComplement());
            $this->SetValueToFormField($form, 'zipCode', $thisInstitution->getZipCode());
            $this->SetValueToFormField($form, 'district', $thisInstitution->getDistrict());
            $this->SetValueToFormField($form, 'city', $thisInstitution->getCity());
            $this->SetValueToFormField($form, 'state', $thisInstitution->getState());
            $this->SetValueToFormField($form, 'website', $thisInstitution->getWebsite());
            $this->SetValueToFormField($form, 'type', $thisInstitution->getType());
            $this->SetValueToFormField($form, 'relationshipType', $thisInstitution->getRelationshipType());

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
        $actionMapper = new C3op_Register_ActionMapper($this->db);

        $id = $this->checkIdFromGet();
        $thisInstitution = $this->institutionMapper->findById($id);
        $productsIdList = $this->institutionMapper->getAllProducts($thisInstitution);
        if (count($productsIdList) > 0) {
            $linkReceivings = '/register/institution/receivings/?id=' . $thisInstitution->GetId();
        } else {
            $linkReceivings = "";
        }

        $actionsIdsList = $this->institutionMapper->getAllActions($thisInstitution);
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
                'linkEdit' => '/register/action/edit/?id=' . $actionId   ,
            );
        }
        $institutionInfo = array(
            'name' => $thisInstitution->GetName(),
            'linkEdit' => '/register/institution/edit/?id=' . $id   ,
            'linkReceivings' => $linkReceivings,
            'dateBegin' => $thisInstitution->GetDateBegin(),
            'value' => $thisInstitution->GetValue(),
            'linkActionCreate' => '/register/action/create/?institution=' . $id,
            'actionsList' => $actionsList,
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

    public function receivingsAction()
    {
        $actionMapper = new C3op_Register_ActionMapper($this->db);

        $id = $this->checkIdFromGet();
        $thisInstitution = $this->institutionMapper->findById($id);
        $productsIdList = $this->institutionMapper->getAllProducts($thisInstitution);
        $productsList = array();
        reset ($productsList);
        foreach ($productsIdList as $actionId) {
            $thisAction = $actionMapper->findById($actionId);

            $productsList[$actionId] = array(
                'name' => $thisAction->GetName(),
                'linkEdit' => '/register/action/edit/?id=' . $actionId   ,
            );
        }
        $institutionInfo = array(
            'name' => $thisInstitution->GetName(),
            'linkEdit' => '/register/institution/edit/?id=' . $id   ,
            'productsList' => $productsList,
        );

        $this->view->institutionInfo = $institutionInfo;
    }
    
    private function setValueToFormField(C3op_Form_InstitutionCreate $form, $fieldName, $value)
    {
        $field = $form->getElement($fieldName);
        $field->setValue($value);
    }

}