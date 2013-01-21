<?php

class Projects_MaterialSupplyController extends Zend_Controller_Action
{
    private $materialSupplyMapper;
    private $actionMapper;
    private $contactMapper;
    private $institutionMapper;
    private $linkageMapper;
    private $db;

    public function preDispatch()
    {
        try {
            $checker = new C3op_Access_PrivilegeChecker();
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function init()
    {
        $this->db = Zend_Registry::get('db');
    }

    public function createAction()
    {
        // cria form
        $form = new C3op_Form_MaterialSupplyCreate();
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/projects/material-supply/success/?id=' . $id);
            } else {
                //form error: populate and go back
                $form->populate($postData);
                $this->view->form = $form;
            }
        } else {
            $data = $this->_request->getParams();
            $actionId = $data['actionId'];
            if (!isset($this->actionMapper)) {
                $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
            }
            if (!isset($this->projectMapper)) {
                $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
            }
            $parentAction = $this->actionMapper->findById($actionId);
            $projectAction = $this->projectMapper->findById($parentAction->getProject());

            $pageData = array(
                'actionId' => $actionId,
                'actionTitle' => $parentAction->GetTitle(),
                'projectId' => $parentAction->GetProject(),
                'projectTitle' => $projectAction->GetShortTitle(),
            );

            $this->view->pageData = $pageData;

            $actionField = $form->getElement('action');
            $actionField->setValue($actionId);
            $institutionField = $form->getElement('institution');
            if (!isset($this->institutionMapper)) {
                $this->institutionMapper = new C3op_Register_InstitutionMapper($this->db);
            }
            $allInstitutions = $this->institutionMapper->getAllServiceSuppliers();

            while (list($key, $institutionData) = each($allInstitutions)) {
                $institutionLabel = $institutionData['short_name'];
                $institutionField->addMultiOption($institutionData['id'], $institutionLabel);
            }
            $linkageField = $form->getElement('linkage');
        }
    }

    public function editAction()
    {
        $form = new C3op_Form_MaterialSupplyEdit;
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/projects/material-supply/success/?id=' . $id);
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
                if (!isset($this->materialSupplyMapper)) {
                    $this->materialSupplyMapper = new C3op_Projects_MaterialSupplyMapper($this->db);
                }
                $thisMaterialSupply = $this->materialSupplyMapper->findById($id);
                $descriptionField = $form->getElement('description');
                $descriptionField->setValue($thisMaterialSupply->getDescription());

                $institutionField = $form->getElement('institution');
                if (!isset($this->institutionMapper)) {
                    $this->institutionMapper = new C3op_Register_InstitutionMapper($this->db);
                }
                $allInstitutions = $this->institutionMapper->getAllServiceSuppliers();

                while (list($key, $institutionData) = each($allInstitutions)) {
                    $institutionLabel = $institutionData['short_name'];
                    $institutionField->addMultiOption($institutionData['id'], $institutionLabel);
                }
                $institutionField->setValue($thisMaterialSupply->getInstitution());

                $linkageField = $form->getElement('linkage');


                if ($thisMaterialSupply->getInstitution() > 0) {
                    if (!isset($this->contactMapper)) {
                        $this->initContactMapper();
                    }

                    $contactsList = $this->institutionMapper->getAllContactsThatAreLinkedToAnInstitution($thisMaterialSupply->getInstitution());

                    foreach ($contactsList as $key => $contactData) {
                        $loopContact = $this->contactMapper->findById($contactData['id']);
                        $name = $loopContact->getName();
                        $linkageField->addMultiOption($id, $name);
                    }

                    $linkageField->setValue($thisMaterialSupply->getLinkage());
                } else {
                    $linkageField->setValue(0);

                }
                $idField = $form->getElement('id');
                $idField->setValue($id);
                $element = $form->getElement('unitValue');
                $element->setValue($thisMaterialSupply->getUnitValue());
                $element = $form->getElement('quantity');
                $element->setValue($thisMaterialSupply->getQuantity());
                $element = $form->getElement('unit');
                $element->setValue($thisMaterialSupply->getUnit());
                $actionField = $form->getElement('action');
                $actionField->setValue($thisMaterialSupply->getAction());
                if (!isset($this->actionMapper)) {
                    $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
                }
                $parentAction = $this->actionMapper->findById($thisMaterialSupply->getAction());


                if (!isset($this->projectMapper)) {
                    $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
                }
                $projectAction = $this->projectMapper->findById($parentAction->getProject());

                $pageData = array(
                    'actionId' => $thisMaterialSupply->GetAction(),
                    'actionTitle' => $parentAction->GetTitle(),
                    'projectId' => $parentAction->GetProject(),
                    'projectTitle' => $projectAction->GetShortTitle(),
                );

                $this->view->pageData = $pageData;
            }
        }
    }

    public function contractAction()
    {
        $form = new C3op_Form_MaterialSupplyContract;
        $headerData = array();
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/projects/material-supply/success/?id=' . $id);
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
                $this->initMaterialSupplyMapper();
                $thisMaterialSupply = $this->materialSupplyMapper->findById($id);

                $idField = $form->getElement('id');
                $idField->setValue($id);
                $this->initActionMapper();
                $thisAction = $this->actionMapper->findById($thisMaterialSupply->getAction());

                $this->setDateValueToFormField($form, 'predictedBeginDate', $thisAction->GetPredictedBeginDate());
                $this->setDateValueToFormField($form, 'predictedFinishDate', $thisAction->GetPredictedFinishDate());
                $contactField = $form->getElement('contact');
                $this->initContactMapper();
                $this->initLinkageMapper();

                $allContacts = $this->contactMapper->getAllIds();
                $linkage = $this->linkageMapper->findById($thisMaterialSupply->getLinkage());
                $contact = $this->contactMapper->findById($linkage->getContact());
                $contactName = $contact->GetName();

                $actionTitle = $thisAction->GetTitle();
               $headerData = array(
                   'materialSupplyName' => $contactName,
                   'actionTitle'    => $actionTitle,
               ) ;

            }

        }
        $this->view->headerData = $headerData;
    }

    public function outlaysAction()
    {
        $outlayMapper = new C3op_Finances_OutlayMapper($this->db);
        if (!isset($this->materialSupplyMapper)) {
            $this->materialSupplyMapper = new C3op_Projects_MaterialSupplyMapper($this->db);
        }

        $id = $this->checkIdFromGet();
        $thisMaterialSupply = $this->materialSupplyMapper->findById($id);
        $outlaysIdList = $this->materialSupplyMapper->getAllOutlays($thisMaterialSupply);
        $outlaysList = array();
        reset ($outlaysList);
        $outlaysTotalValue = 0;
        $outlaysCounter = 0;
        foreach ($outlaysIdList as $outlayId) {
            $thisOutlay = $outlayMapper->findById($outlayId);
            $outlaysCounter++;
            if ($thisOutlay->GetObservation()) {
                $observation = $thisOutlay->GetObservation();
            } else {
                $observation = "(#$outlaysCounter)";
            }

            if ($thisOutlay->GetPredictedValue()) {
                $value = $thisOutlay->GetPredictedValue();
            } else {
                $value = "???";
            }



            $outlaysList[$outlayId] = array(
                'observation' => $observation,
                'value' => $value,
                'editLink' => '/finances/outlay/edit/?id=' . $outlayId   ,
            );
        }

        $materialSupplyInfo = array(
            'title' => 'provisório...',
            'linkDetail' => '/projects/project/detail/?id=' . 0 ,
            'outlaysList' => $outlaysList,
        );

        $this->view->materialSupplyInfo = $materialSupplyInfo;
    }

    public function removeAction()
    {
        $form = new C3op_Form_MaterialSupplyRemove();
        $this->view->form = $form;
        $this->initMaterialSupplyMapper();
        $this->initActionMapper();

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $submitButton = $form->getUnfilteredValue('Submit');

                if ($submitButton) {
                    $theMaterialSupply = $this->materialSupplyMapper->findById($postData['id']);
                    $theAction = $this->actionMapper->findById($theMaterialSupply->getAction());
                    $projectMaterialSupply = $theAction->getProject();
                    $redirectTo = '/projects/project/detail/?id=' . $projectMaterialSupply;

                    $id = $form->process($postData);
                    $this->_helper->getHelper('FlashMessenger')
                        ->addMessage($this->view->translate('#The record was successfully removed.'));
                    $this->_redirect($redirectTo);
                } else {
                    $id = $postData['id'];
                    $theMaterialSupply = $this->materialSupplyMapper->findById($id);
                    $theAction = $this->actionMapper->findById($theMaterialSupply->getAction());
                    $this->_redirect('/projects/project/detail/?id=' . $theAction->getProject());
                }
            } else {
                //form error: populate and go back
                $this->view->form = $form;
            }
        } else {
            // GET
            $id = $this->checkIdFromGet();
            $materialSupplyData = array();
            $theMaterialSupply = $this->materialSupplyMapper->findById($id);
            $theAction = $this->actionMapper->findById($theMaterialSupply->getAction());
            $idField = $form->getElement('id');
            $idField->setValue($id);
            $materialSupplyData = array(
                'id'   => $id,
                'description' => $theMaterialSupply->getDescription(),
                'actionTitle' => $theAction->getTitle(),
            );
            $this->view->materialSupplyData = $materialSupplyData;
        }
    }

     public  function populateResponsibleAtSupplierAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $id = $this->checkIdFromGet();
        if (!isset($this->institutionMapper)) {
            $this->initInstitutionMapper();
        }
        if (!isset($this->contactMapper)) {
            $this->initContactMapper();
        }

        $contactsList = $this->institutionMapper->getAllContactsThatAreLinkedToAnInstitution($id);
        $data = array();
        foreach ($contactsList as $k => $id) {
            $loopContact = $this->contactMapper->findById($id);
            $data[] = array('id' => $id, 'title' => $loopContact->getName());
        }

        echo json_encode($data);


    }

   public function successAction()
    {
        $this->initMaterialSupplyMapper();
        $materialSupply =  $this->initMaterialSupplyWithCheckedId($this->materialSupplyMapper);
        $actionRelated = $materialSupply->GetAction();
        if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();
            $this->getResponse()->setHeader('Refresh', '1; URL=/projects/action/detail/?id=' . $actionRelated);
        } else {
            $this->_redirect('/projects');
        }
    }

   public function dismissContactAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $this->initMaterialSupplyMapper();
        $this->initActionMapper();
        $materialSupply =  $this->initMaterialSupplyWithCheckedId($this->materialSupplyMapper);
        $action = $this->actionMapper->findById($materialSupply->GetAction());
        $dismissal = new C3op_Projects_MaterialSupplyDismissal();
        $dismissal->ContactDismiss($action, $materialSupply, $this->materialSupplyMapper);

        echo 'Contato dispensado';
    }

    private function initMaterialSupplyWithCheckedId(C3op_Projects_MaterialSupplyMapper $mapper)
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
        throw new C3op_Projects_ProjectException("Invalid Project Id from Get");

    }

    private function populateContactsField($materialSupplyId, C3op_Form_MaterialSupplyCreate $form, $contactId = 0)
    {
        $validator = new C3op_Util_ValidId();
        $parentActionId = 0;
        if ($validator->isValid($materialSupplyId)) {
            $subordinatedToField = $form->getElement('subordinatedTo');
            if (!isset($this->actionMapper)) {
                $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
            }

            if ($contactId > 0) {
                $thisAction = $this->actionMapper->findById($contactId);
                $parentActionId = $thisAction->GetSubordinatedTo();
                $allOtherActionsInProject = $this->actionMapper->getAllOtherActions($thisAction);

            } else {
                if (!isset($this->projectMapper)) {
                    $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
                }
                $thisProject = $this->projectMapper->findById($materialSupplyId);
                $allOtherActionsInProject = $this->projectMapper->getAllActions($thisProject);
            }

            while (list($key, $contactId) = each($allOtherActionsInProject)) {
                $eachAction = $this->actionMapper->findById($contactId);
                $subordinatedToField->addMultiOption($contactId, $eachAction->GetTitle());
            }

            $subordinatedToField->setValue($parentActionId);

        } else throw new C3op_Projects_ActionException("Action needs a positive integer project id to find other actions.");
   }

    private function initActionMapper()
    {
         $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
    }

   private function initMaterialSupplyMapper()
    {
         $this->materialSupplyMapper = new C3op_Projects_MaterialSupplyMapper($this->db);
    }

    private function initContactMapper()
    {
         $this->contactMapper = new C3op_Register_ContactMapper($this->db);
    }

    private function initInstitutionMapper()
    {
         $this->institutionMapper = new C3op_Register_InstitutionMapper($this->db);
    }

    private function initLinkageMapper()
    {
         $this->linkageMapper = new C3op_Register_LinkageMapper($this->db);
    }

    private function initActionWithCheckedId(C3op_Projects_ActionMapper $mapper)
    {
        return $mapper->findById($this->checkIdFromGet());
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