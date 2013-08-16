<?php

class Resources_ResponsibleController extends Zend_Controller_Action
{
    private $responsibleMapper;
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

    public function assignedAction()
    {
        $this->_helper->layout->disableLayout();

        $data = $this->_request->getParams();

        $id = $data['id'];

        $pageData = array(
            'responsibleType'  => null,
            'responsibleId'    => null,
            'contactId'        => null,
            'contactName'      => $this->view->translate('#(undefined)'),
            'responsibleLabel' => $this->view->translate('#(undefined)'),
            'projectId'        => null,
        );

        if ($id > 0) {

            $this->initResponsibleMapper();
            $responsible = $this->responsibleMapper->findById($id);

            $this->initActionMapper();
            $action = $this->actionMapper->findById($responsible->getAction());

            $obj = new C3op_Projects_ActionResponsible($action, $this->actionMapper, $this->db);
            if ($obj->doesItHaveAResponsible()) {
                $result = $obj->fetch();
                $pageData = array(
                    'responsibleType'  => $result['responsibleType'],
                    'responsibleId'    => $result['responsibleId'],
                    'contactId'        => $result['contactId'],
                    'contactName'      => $result['contactName'],
                    'responsibleLabel' => $result['responsibleLabel'],
                    'projectId'        => $action->GetProject(),
                );
            }
        }

        $this->view->pageData = $pageData;

    }

    public function contractAction()
    {
        $form = new C3op_Form_ResponsibleContract;
        $headerData = array();
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->initResponsibleMapper();
                $responsible =  $this->responsibleMapper->findById($id);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The team member is contracted.'));
                $this->_redirect('/projects/action/detail/?id=' . $responsible->GetAction());
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
                $this->initResponsibleMapper();
                $thisResponsible = $this->responsibleMapper->findById($id);

                $idField = $form->getElement('id');
                $idField->setValue($id);
                $this->initActionMapper();
                $thisAction = $this->actionMapper->findById($thisResponsible->getAction());



                $this->setDateValueToFormField($form, 'predictedBeginDate', $thisAction->GetPredictedBeginDate());
                $this->setDateValueToFormField($form, 'predictedFinishDate', $thisAction->GetPredictedFinishDate());
                $contactField = $form->getElement('contact');
                $this->initContactMapper();
                $this->initLinkageMapper();

                $allContacts = $this->contactMapper->getAllIds();
                $contact = $this->contactMapper->findById($thisResponsible->getContact());
                $contactName = $contact->GetName();

                $actionTitle = $thisAction->GetTitle();

                $currencyDisplay = new  C3op_Util_CurrencyDisplay();
                $actionBudgetValue = $thisResponsible->GetPredictedValue();
                $currencyValue = $currencyDisplay->FormatCurrency($actionBudgetValue);
                $providedBudget = sprintf($this->view->translate("#A %s budget was provided"), $currencyValue);

                $valueField = $form->getElement('value');
                $currencyDisplay = new  C3op_Util_CurrencyDisplay();
                $actionValue = $thisResponsible->GetContractedValue();
                if ($actionValue == 0) {
                    $actionValue = $actionBudgetValue;
                }
                $currencyValue = $currencyDisplay->FormatCurrency($actionValue);
                $valueField->setValue($currencyValue);


               $headerData = array(
                   'actionId'        => $thisAction->getId(),
                   'responsibleName' => $contactName,
                   'providedBudget'  => $providedBudget,
                   'actionTitle'     => $actionTitle,
               ) ;

            }

        }
        $this->view->headerData = $headerData;
    }



    public function createAction()
    {
        $this->_helper->layout->disableLayout();

        // cria form
        $form = new C3op_Form_ResponsibleCreate;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->initResponsibleMapper();
                $responsible =  $this->responsibleMapper->findById($id);
                //$this->_helper->viewRenderer->setNoRender(TRUE);
                //$this->_redirect('/resources/responsible/assigned/?id=' . $id);
                $this->_helper->getHelper('FlashMessenger')
                     ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/projects/action/detail/?id=' . $responsible->GetAction());
            } else {
                //form error: populate and go back
                $actionId = $postData['action'];
                $result = $this->givenActionIdGetActionAndProjectObjects($actionId);
                $parentAction = $result['actionObj'];
                $projectAction = $result['projectObj'];

                $form->populate($postData);
                $this->view->form = $form;
            }
        } else {

            $user = Zend_Registry::get('user');
            $test = new C3op_Access_UserCanSeeFinances($user);
            if ($test->can()) {
                $canSeeFinances = true;
            } else {
                $canSeeFinances = false;
            }

            $data = $this->_request->getParams();

            $actionId = $data['actionId'];

            if (!isset($this->actionMapper)) {
                $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
            }
            if (!isset($this->projectMapper)) {
                $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
            }
            $parentAction = $this->actionMapper->findById($actionId);
            $projectId = $parentAction->getProject();
            $projectAction = $this->projectMapper->findById($projectId);

            $element = $form->getElement('action');
            $element->setValue($actionId);
            $element = $form->getElement('project');
            $element->setValue($projectId);
            $element = $form->getElement('linkage');
            if (!isset($this->contactMapper)) {
                $this->contactMapper = new C3op_Register_ContactMapper($this->db);
            }
            $allLinkedContacts = $this->contactMapper->getAllContactsThatAreLinkedToAnyInstitution();

            while (list($key, $linkageData) = each($allLinkedContacts)) {
                $linkageLabel = $linkageData['name'] . " ({$linkageData['short_name']})";
                $element->addMultiOption($linkageData['linkage'], $linkageLabel);
            }

            $element = $form->getElement('institution');
            if (!isset($this->institutionMapper)) {
                $this->institutionMapper = new C3op_Register_InstitutionMapper($this->db);
            }
            $allInstitutions = $this->institutionMapper->getAllServiceSuppliers();

            while (list($key, $institutionData) = each($allInstitutions)) {
                $institutionLabel = $institutionData['short_name'];
                $element->addMultiOption($institutionData['id'], $institutionLabel);
            }

            if ($canSeeFinances) {
                $element = $form->getElement('predictedValue');
                $element->setValue($parentAction->getBudgetForecast());

            } else {
                $form->removeElement('predictedValue');
            }

            $finder = new C3op_Projects_ActionRelatedProduct($parentAction,$this->actionMapper);
            $productObj = $finder->retrieve();
            $actionBalance = new  C3op_Finances_ActionBalance($productObj,$this->actionMapper);
            $rawBalance = $actionBalance->getBalance();
            $currencyDisplay = new  C3op_Util_CurrencyDisplay();
            $balance = $currencyDisplay->FormatCurrency($rawBalance);


        }

        $pageData = array(
            'actionId' => $actionId,
            'actionTitle' => $parentAction->GetTitle(),
            'projectId' => $parentAction->GetProject(),
            'projectTitle' => $projectAction->GetShortTitle(),
            'balance' => $balance,
            'canSeeFinances' => $canSeeFinances,
        );

        $this->view->pageData = $pageData;

    }


    public function editAction()
    {
        $this->_helper->layout->disableLayout();
        $form = new C3op_Form_ResponsibleEdit;
        $this->view->form = $form;

        $user = Zend_Registry::get('user');
        $test = new C3op_Access_UserCanSeeFinances($user);
        if ($test->can()) {
            $canSeeFinances = true;
        } else {
            $canSeeFinances = false;
        }


        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                if ($id > 0) {
                    $this->initResponsibleMapper();
                    $responsible =  $this->responsibleMapper->findById($id);
                    //$this->_helper->viewRenderer->setNoRender(TRUE);
                    //$this->_redirect('/resources/responsible/assigned/?id=' . $id);

                    $this->_helper->getHelper('FlashMessenger')
                         ->addMessage($this->view->translate('#The record was successfully updated.'));
                    $this->_redirect('/projects/action/detail/?id=' . $responsible->GetAction());
                } else {
                    //$this->_helper->viewRenderer->setNoRender(TRUE);
                    //$this->_redirect('/resources/responsible/assigned/?id=0');
                    $this->_helper->getHelper('FlashMessenger')
                         ->addMessage($this->view->translate('#The record was successfully updated.'));
                    $this->_redirect('/projects/action/detail/?id=' . $postData['action']);

                }
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
                if (!isset($this->responsibleMapper)) {
                    $this->responsibleMapper = new C3op_Resources_ResponsibleMapper($this->db);
                }
                $thisResponsible = $this->responsibleMapper->findById($id);
                $element = $form->getElement('id');
                $element->setValue($id);

                $element = $form->getElement('action');
                $element->setValue($thisResponsible->getAction());
                $element = $form->getElement('project');
                $element->setValue($thisResponsible->getProject());
                if (!isset($this->actionMapper)) {
                    $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
                }
                $parentAction = $this->actionMapper->findById($thisResponsible->getAction());

                $contractedValue = null;
                $contracting = new C3op_Resources_ResponsibleContracting();
                $isContracted = $contracting->isUnderContract($thisResponsible);
                if ($isContracted) {
                    $contractedValue = $thisResponsible->getContractedValue();

                }
                if ($canSeeFinances) {
                    $finder = new C3op_Projects_ActionRelatedProduct($parentAction,$this->actionMapper);
                    $productObj = $finder->retrieve();
                    $actionBalance = new  C3op_Finances_ActionBalance($productObj,$this->actionMapper);
                    $rawBalance = $actionBalance->getBalance();
                    $currencyDisplay = new  C3op_Util_CurrencyDisplay();
                    $balance = $currencyDisplay->FormatCurrency($rawBalance);

                    $element = $form->getElement('contractedValue');
                    $element->setValue($contractedValue);
                    $element = $form->getElement('predictedValue');
                    $element->setValue($thisResponsible->getPredictedValue());


                } else {
                    $form->removeElement('predictedValue');
                    $balance = null;
                }



                $element = $form->getElement('responsibleType');
                $responsibleType = $thisResponsible->getType();
                if ($responsibleType == C3op_Resources_ResponsibleTypeConstants::TYPE_TEAM_MEMBER) {
                    $element->setValue("teamMember");
                    if (!isset($this->linkageMapper)) {
                        $this->linkageMapper = new C3op_Register_LinkageMapper($this->db);
                    }
                    $linkage = $this->linkageMapper->findByContactAndInstitution($thisResponsible->getContact(), $thisResponsible->getInstitution());
                    $element = $form->getElement('linkage');
                    if (!isset($this->contactMapper)) {
                        $this->contactMapper = new C3op_Register_ContactMapper($this->db);
                    }
                    $allLinkedContacts = $this->contactMapper->getAllContactsThatAreLinkedToAnyInstitution();

                    while (list($key, $linkageData) = each($allLinkedContacts)) {
                        $linkageLabel = $linkageData['name'] . " ({$linkageData['short_name']})";
                        $element->addMultiOption($linkageData['linkage'], $linkageLabel);
                    }
                    if ($linkage) {
                        $element->setValue($linkage->getId());
                        $element = $form->getElement('institution');
                        if ($linkage->getInstitution() > 0) {
                            if (!isset($this->institutionMapper)) {
                                $this->institutionMapper = new C3op_Register_InstitutionMapper($this->db);
                            }
                            $linkageInstitution = $this->institutionMapper->findById($linkage->getInstitution());
                            $element->addMultiOption($linkage->getInstitution(), $linkageInstitution->getShortName());
                            $element->SetValue($linkage->getInstitution());
                        }

                    }

                } else if ($responsibleType == C3op_Resources_ResponsibleTypeConstants::TYPE_OUTSIDE_SERVICE) {

                    $element = $form->getElement('institution');
                    if (!isset($this->institutionMapper)) {
                        $this->institutionMapper = new C3op_Register_InstitutionMapper($this->db);
                    }
                    $allInstitutions = $this->institutionMapper->getAllServiceSuppliers();
                    while (list($key, $institutionData) = each($allInstitutions)) {
                        $institutionLabel = $institutionData['short_name'];
                        $element->addMultiOption($institutionData['id'], $institutionLabel);
                    }
                    $element->setValue($thisResponsible->getInstitution());


                    $element = $form->getElement('linkage');
                    if ($thisResponsible->getInstitution() > 0) {
                        if (!isset($this->contactMapper)) {
                            $this->initContactMapper();
                        }

                        $contactsList = $this->institutionMapper->getAllContactsThatAreLinkedToAnInstitution($thisResponsible->getInstitution());
                        foreach ($contactsList as $key => $contactData) {
                            $contactId = $contactData['contactId'];
                            $linkageId = $contactData['linkageId'];
                            $loopContact = $this->contactMapper->findById($contactId);
                            $name = $loopContact->getName();
                            $element->addMultiOption($linkageId, $name);
                        }

                        if ($thisResponsible->getInstitution() > 0) {
                            if (!isset($this->linkageMapper)) {
                                $this->initLinkageMapper();
                            }
                            if ($thisResponsible->getContact() > 0) {
                                $linkage = $this->linkageMapper->findByContactAndInstitution($thisResponsible->getContact(), $thisResponsible->getInstitution());
                                $linkageId = $linkage->getId();
                            } else {
                                $linkageId = 0;
                            }
                        } else {
                            $linkageId = 0;

                        }
                        $element->setValue($linkageId);
                    } else {
                        $element->setValue(0);
                    }
                    $element->setValue("service");
                }

                if (!isset($this->linkageMapper)) {
                    $this->linkageMapper = new C3op_Register_LinkageMapper($this->db);
                }

                if (!isset($this->projectMapper)) {
                    $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
                }
                $projectAction = $this->projectMapper->findById($parentAction->getProject());

            }



            $pageData = array(
                'actionId'       => $thisResponsible->GetAction(),
                'actionTitle'    => $parentAction->GetTitle(),
                'projectId'      => $parentAction->GetProject(),
                'projectTitle'   => $projectAction->GetShortTitle(),
                'canSeeFinances' => $canSeeFinances,
                'balance'        => $balance,
                'isContracted'   => $isContracted,
            );

            $this->view->pageData = $pageData;
        }
    }

    public function dismissAction()
    {
        $form = new C3op_Form_ResponsibleDismiss;
        $headerData = array();
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->initResponsibleMapper();
                $responsible =  $this->responsibleMapper->findById($id);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The team member was dismissed.'));
                $this->_redirect('/projects/action/detail/?id=' . $responsible->GetAction());
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
                $this->initResponsibleMapper();
                $thisResponsible = $this->responsibleMapper->findById($id);

                if ($thisResponsible->getStatus() != C3op_Resources_ResponsibleStatusConstants::STATUS_CONTRACTED) {

                    throw new C3op_Projects_ProjectException("Can\' dismiss a team member that is not contracted.");

                }

                $element = $form->getElement('id');
                $element->setValue($id);
                $this->initActionMapper();
                $thisAction = $this->actionMapper->findById($thisResponsible->getAction());

                $this->initContactMapper();

                $contact = $this->contactMapper->findById($thisResponsible->getContact());
                $contactName = $contact->GetName();

                $actionTitle = $thisAction->GetTitle();
                $headerData = array(
                   'responsibleName' => $contactName,
                   'actionTitle'    => $actionTitle,
                ) ;

            }

        }
        $this->view->headerData = $headerData;
    }



     public function populateContactsFieldAction()
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

        if ($id > 0) {
            $linkagesList = $this->institutionMapper->getAllLinkagesAtAnInstitution($id);
            $selectedInstitution = $this->institutionMapper->findById($id);

        } else {
            $linkagesList = $this->contactMapper->getAllContactsThatAreLinkedToAnyInstitution();

        }
        $data = array();
        foreach ($linkagesList as $k => $linkageData) {
            $loopLinkage = $this->linkageMapper->findById($linkageData['linkage']);
            $loopContact = $this->contactMapper->findById($loopLinkage->getContact());
            if ($id == 0) {
                $loopInstitution = $this->institutionMapper->findById($loopLinkage->getInstitution());
                $institutionName = $loopInstitution->getShortName();
                $linkageLabel = $loopContact->getName() . " ($institutionName)";
            } else {
                $linkageLabel = $loopContact->getName();
            }
            $data[] = array('id' => $loopLinkage->getId(), 'name' => $linkageLabel);
        }

        echo json_encode($data);


    }

     public function populateInstitutionsFieldAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        if (!isset($this->institutionMapper)) {
            $this->initInstitutionMapper();
        }
        if (!isset($this->linkageMapper)) {
            $this->initLinkageMapper();
        }

        if (!isset($this->contactMapper)) {
            $this->initContactMapper();
        }


        if (!isset($this->institutionMapper)) {
            $this->institutionMapper = new C3op_Register_InstitutionMapper($this->db);
        }
        $allInstitutions = $this->institutionMapper->getAllServiceSuppliers();

        $data = array();
        while (list($key, $institutionData) = each($allInstitutions)) {
            $data[] = array('id' => $institutionData['id'], 'name' => $institutionData['short_name']);
        }

        echo json_encode($data);


    }


    private function checkIdFromGet()
    {
        $data = $this->_request->getParams();
        $filters = array(
            'id' => new Zend_Filter_Alnum(),
        );
        $validators = array(
            'id' => array('Digits', new C3op_Util_ValidPositiveInteger),
        );
        $input = new Zend_Filter_Input($filters, $validators, $data);
        if ($input->isValid()) {
            $id = $input->id;
            return $id;
        }
        throw new C3op_Projects_ProjectException("Invalid Project Id from Get");

    }

    private function initActionMapper()
    {
         $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
    }

   private function initResponsibleMapper()
    {
         $this->responsibleMapper = new C3op_Resources_ResponsibleMapper($this->db);
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

   private function givenActionIdGetActionAndProjectObjects($actionId)
    {
        if (!isset($this->actionMapper)) {
            $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
        }
        if (!isset($this->projectMapper)) {
            $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
        }
        $parentAction = $this->actionMapper->findById($actionId);
        $projectId = $parentAction->getProject();
        $projectAction = $this->projectMapper->findById($projectId);

        $data = array(
                    'actionId' => $actionId,
                    'actionObj' => $parentAction,
                    'projectId' => $projectId,
                    'projectObj' => $projectAction,
                );

        return $data;


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
