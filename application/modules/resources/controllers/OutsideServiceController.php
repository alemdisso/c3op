<?php

class Resources_OutsideServiceController extends Zend_Controller_Action
{
    private $outsideServiceMapper;
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
        $form = new C3op_Form_OutsideServiceCreate();
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/resources/outside-service/success/?id=' . $id);
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
        $form = new C3op_Form_OutsideServiceEdit;
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/resources/outside-service/success/?id=' . $id);
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
                if (!isset($this->outsideServiceMapper)) {
                    $this->outsideServiceMapper = new C3op_Resources_OutsideServiceMapper($this->db);
                }
                $thisOutsideService = $this->outsideServiceMapper->findById($id);
                $descriptionField = $form->getElement('description');
                $descriptionField->setValue($thisOutsideService->getDescription());

                $institutionField = $form->getElement('institution');
                if (!isset($this->institutionMapper)) {
                    $this->institutionMapper = new C3op_Register_InstitutionMapper($this->db);
                }
                $allInstitutions = $this->institutionMapper->getAllServiceSuppliers();

                while (list($key, $institutionData) = each($allInstitutions)) {
                    $institutionLabel = $institutionData['short_name'];
                    $institutionField->addMultiOption($institutionData['id'], $institutionLabel);
                }
                $institutionField->setValue($thisOutsideService->getInstitution());

                $linkageField = $form->getElement('linkage');


                if ($thisOutsideService->getInstitution() > 0) {
                    if (!isset($this->contactMapper)) {
                        $this->initContactMapper();
                    }

                    $contactsList = $this->institutionMapper->getAllContactsThatAreLinkedToAnInstitution($thisOutsideService->getInstitution());

                    foreach ($contactsList as $key => $contactData) {
                        $loopContact = $this->contactMapper->findById($contactData['id']);
                        $name = $loopContact->getName();
                        $linkageField->addMultiOption($id, $name);
                    }

                    $linkageField->setValue($thisOutsideService->getLinkage());
                } else {
                    $linkageField->setValue(0);

                }
                $idField = $form->getElement('id');
                $idField->setValue($id);
                $valueField = $form->getElement('value');
                $valueField->setValue($thisOutsideService->getValue());
                $actionField = $form->getElement('action');
                $actionField->setValue($thisOutsideService->getAction());
                if (!isset($this->actionMapper)) {
                    $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
                }
                $parentAction = $this->actionMapper->findById($thisOutsideService->getAction());


                if (!isset($this->projectMapper)) {
                    $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
                }
                $projectAction = $this->projectMapper->findById($parentAction->getProject());

                $pageData = array(
                    'actionId' => $thisOutsideService->GetAction(),
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
        $form = new C3op_Form_OutsideServiceContract;
        $headerData = array();
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/resources/outside-service/success/?id=' . $id);
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
                $this->initOutsideServiceMapper();
                $thisOutsideService = $this->outsideServiceMapper->findById($id);

                $idField = $form->getElement('id');
                $idField->setValue($id);
                $this->initActionMapper();
                $thisAction = $this->actionMapper->findById($thisOutsideService->getAction());

                $this->setDateValueToFormField($form, 'predictedBeginDate', $thisAction->GetPredictedBeginDate());
                $this->setDateValueToFormField($form, 'predictedFinishDate', $thisAction->GetPredictedFinishDate());
                $contactField = $form->getElement('contact');
                $this->initContactMapper();
                $this->initLinkageMapper();

                $allContacts = $this->contactMapper->getAllIds();
                $linkage = $this->linkageMapper->findById($thisOutsideService->getLinkage());

                $contact = $this->contactMapper->findById($linkage->getContact());
                $contactName = $contact->GetName();

                $actionTitle = $thisAction->GetTitle();
               $headerData = array(
                   'outsideServiceName' => $contactName,
                   'actionTitle'    => $actionTitle,
               ) ;

            }

        }
        $this->view->headerData = $headerData;
    }

    public function outlaysAction()
    {
        $outlayMapper = new C3op_Finances_OutlayMapper($this->db);
        if (!isset($this->outsideServiceMapper)) {
            $this->outsideServiceMapper = new C3op_Resources_OutsideServiceMapper($this->db);
        }

        $id = $this->checkIdFromGet();
        $thisOutsideService = $this->outsideServiceMapper->findById($id);
        $outlaysIdList = $this->outsideServiceMapper->getAllOutlays($thisOutsideService);
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

        $outsideServiceInfo = array(
            'title' => 'provisório...',
            'linkDetail' => '/projects/project/detail/?id=' . 0 ,
            'outlaysList' => $outlaysList,
        );

        $this->view->outsideServiceInfo = $outsideServiceInfo;
    }

    public function removeAction()
    {
        $form = new C3op_Form_OutsideServiceRemove();
        $this->view->form = $form;
        $this->initOutsideServiceMapper();
        $this->initActionMapper();

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $submitButton = $form->getUnfilteredValue('Submit');

                if ($submitButton) {
                    $theOutsideService = $this->outsideServiceMapper->findById($postData['id']);
                    $theAction = $this->actionMapper->findById($theOutsideService->getAction());
                    $projectOutsideService = $theAction->getProject();
                    $redirectTo = '/projects/project/detail/?id=' . $projectOutsideService;

                    $id = $form->process($postData);
                    $this->_helper->getHelper('FlashMessenger')
                        ->addMessage($this->view->translate('#The record was successfully removed.'));
                    $this->_redirect($redirectTo);
                } else {
                    $id = $postData['id'];
                    $theOutsideService = $this->outsideServiceMapper->findById($id);
                    $theAction = $this->actionMapper->findById($theOutsideService->getAction());
                    $this->_redirect('/projects/project/detail/?id=' . $theAction->getProject());
                }
            } else {
                //form error: populate and go back
                $this->view->form = $form;
            }
        } else {
            // GET
            $id = $this->checkIdFromGet();
            $outsideServiceData = array();
            $theOutsideService = $this->outsideServiceMapper->findById($id);
            $theAction = $this->actionMapper->findById($theOutsideService->getAction());
            $idField = $form->getElement('id');
            $idField->setValue($id);
            $outsideServiceData = array(
                'id'   => $id,
                'description' => $theOutsideService->getDescription(),
                'actionTitle' => $theAction->getTitle(),
            );
            $this->view->outsideServiceData = $outsideServiceData;
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
        if (!isset($this->linkageMapper)) {
            $this->initLinkageMapper();
        }

        if (!isset($this->contactMapper)) {
            $this->initContactMapper();
        }

        $linkagessList = $this->institutionMapper->getAllLinkagesAtAnInstitution($id);
        $data = array();
        foreach ($linkagessList as $k => $id) {
            $loopLinkage = $this->linkageMapper->findById($id);
            $loopContact = $this->contactMapper->findById($loopLinkage->getContact());
            $data[] = array('id' => $id, 'title' => $loopContact->getName());
        }

        echo json_encode($data);


    }

   public function successAction()
    {
        $this->initOutsideServiceMapper();
        $outsideService =  $this->initOutsideServiceWithCheckedId($this->outsideServiceMapper);
        $actionRelated = $outsideService->GetAction();
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

        $this->initOutsideServiceMapper();
        $this->initActionMapper();
        $outsideService =  $this->initOutsideServiceWithCheckedId($this->outsideServiceMapper);
        $action = $this->actionMapper->findById($outsideService->GetAction());
        $dismissal = new C3op_Resources_OutsideServiceDismissal();
        $dismissal->ContactDismiss($action, $outsideService, $this->outsideServiceMapper);

        echo 'Contato dispensado';
    }

    private function initOutsideServiceWithCheckedId(C3op_Resources_OutsideServiceMapper $mapper)
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

    private function populateContactsField($outsideServiceId, C3op_Form_OutsideServiceCreate $form, $contactId = 0)
    {
        $validator = new C3op_Util_ValidId();
        $parentActionId = 0;
        if ($validator->isValid($outsideServiceId)) {
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
                $thisProject = $this->projectMapper->findById($outsideServiceId);
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

   private function initOutsideServiceMapper()
    {
         $this->outsideServiceMapper = new C3op_Resources_OutsideServiceMapper($this->db);
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
        $validator = new C3op_Util_ValidDate();
        if ((!is_null($value)) && ($validator->isValid($value))) {
            $field->setValue(C3op_Util_DateDisplay::FormatDateToShow($value));
        } else {
            $field->setValue("");
        }
    }


}