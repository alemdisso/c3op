<?php

class Register_InstitutionController extends Zend_Controller_Action
{
    private $institutionMapper;
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
        $this->institutionMapper = new C3op_Register_InstitutionMapper($this->db);
    }

    public function indexAction()
    {
        //  institutiosList
        //   * id =>
        //      name
        //      type
        //      city
        //      state
        //      relationshipType

        $list = $this->institutionMapper->getAllIds();
        $institutionsList = array();
        reset ($list);
        foreach ($list as $id) {
            $thisInstitution = $this->institutionMapper->findById($id);
            $types = new C3op_Register_InstitutionTypes();
            $relationshipTypes = new C3op_Register_RelationshipTypes();

            $institutionsList[$id] = array(
                'name' => $thisInstitution->GetShortName(),
                'type' => $types->TitleForType($thisInstitution->GetType()),
                'name' => $thisInstitution->GetShortName(),
                'city' => $thisInstitution->GetCity(),
                'state' => $thisInstitution->GetState(),
                'relationshipType' => $relationshipTypes->TitleForType($thisInstitution->GetRelationShipType()),
            );
        }

        $pageData = array(
            'institutionsList' => $institutionsList,
        );
        $this->view->pageData = $pageData;
    }

    public function createAction()
    {
        // cria form
        $form = new C3op_Form_InstitutionCreate;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully created.'));
                $this->_redirect('/register/institution/detail/?id=' . $id);
            } else {
               //form error: populate and go back
                $form->populate($postData);
                $this->view->form = $form;
            }
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
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/register/institution/detail/?id=' . $postData['id']);
            } else {
                //form error: populate and go back
                $form->populate($postData);
                $this->view->form = $form;
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

    public function detailAction()
    {
        $linkageMapper = new C3op_Register_LinkageMapper($this->db);
        $contactMapper = new C3op_Register_ContactMapper($this->db);

        $id = $this->checkIdFromGet();
        $thisInstitution = $this->institutionMapper->findById($id);
        $messageToShow = $this->_helper->flashMessenger->getMessages();

        $linkagesIdsList = $this->institutionMapper->getAllLinkages($thisInstitution);
        $linkagesList = array();
        reset ($linkagesList);
        foreach ($linkagesIdsList as $linkageId) {
            $thisLinkage = $linkageMapper->findById($linkageId);
            $thisContact = $contactMapper->findById($thisLinkage->GetContact());

            $linkagesList[$linkageId] = array(
                'contactId' => $thisContact->getId(),
                'contactName' => $thisContact->GetName(),
                'position' => $thisLinkage->GetPosition(),
                'department' => $thisLinkage->GetDepartment(),
            );
        }

        $institutionInfo = array(
            'id' => $id,
            'name' => $thisInstitution->GetName(),
            'linkagesList' => $linkagesList,
        );

        $pageData = array(
            'messageToShow'   => $messageToShow,
            'institutionInfo' => $institutionInfo,
        );
        $this->view->pageData = $pageData;
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
        } else {
            throw new C3op_Register_InstitutionException(_("#Invalid Institution Id from Get"));
        }

    }


}