<?php

class Projects_ActionController extends Zend_Controller_Action
{
    private $actionMapper;
    private $responsibleMapper;
    private $outsideServiceMapper;
    private $projectMapper;
    private $receivableMapper;
    private $contactMapper;
    private $institutionMapper;
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

    public function budgetCreateAction()
    {
        $this->_helper->layout->disableLayout();

        // cria form
        $form = new C3op_Form_BudgetCreate;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $actionId = $form->process($postData);

                $this->_helper->viewRenderer->setNoRender(TRUE);
                $this->_redirect('/projects/action/budget-forecast/?actionId=' . $actionId);
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
            $data = $this->_request->getParams();

            $actionId = $data['id'];

            if (!isset($this->actionMapper)) {
                $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
            }
            if (!isset($this->projectMapper)) {
                $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
            }
            $parentAction = $this->actionMapper->findById($actionId);
            $projectId = $parentAction->getProject();
            $projectAction = $this->projectMapper->findById($projectId);

            $currencyDisplay = new  C3op_Util_CurrencyDisplay();
            $budgetForecast = $currencyDisplay->FormatCurrency($parentAction->getBudgetForecast());

            $actionField = $form->getElement('action');
            $actionField->setValue($actionId);
            $projectField = $form->getElement('project');
            $projectField->setValue($projectId);
            $budgetField = $form->getElement('budgetForecast');
            $budgetField->setValue($budgetForecast);

        }

        $pageData = array(
            'actionId' => $actionId,
            'actionTitle' => $parentAction->GetTitle(),
            'projectId' => $parentAction->GetProject(),
            'projectTitle' => $projectAction->GetShortTitle(),
        );

        $this->view->pageData = $pageData;

    }

    public function budgetForecastAction()
    {
        $this->_helper->layout->disableLayout();

        $data = $this->_request->getParams();

        $actionId = $data['actionId'];

        $this->initActionMapper();
        $action = $this->actionMapper->findById($actionId);

        $currencyDisplay = new  C3op_Util_CurrencyDisplay();
        $budgetForecast = $currencyDisplay->FormatCurrency($action->getBudgetForecast());


        $pageData = array(
            'budgetForecast' => $budgetForecast,
        );

        $this->view->pageData = $pageData;

    }



    public function createAction()
    {
        // cria form
        $form = new C3op_Form_ActionCreate;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully created.'));
                $this->_redirect(sprintf('/projects/action/detail/?id=%d&success=1', $id));
            } else {
                //form error: populate and go back
                $form->populate($postData);
                $this->view->form = $form;
            }
        } else {
            $data = $this->_request->getParams();
            $requirementForReceiving = 0;
            $subordinatedTo = 0;
            if (isset($data['subordinatedTo'])) {
                $isProduct = false;
                $subordinatedTo = $data['subordinatedTo'];
                $this->initActionMapper();
                $parentAction = $this->actionMapper->findById($subordinatedTo);
                $projectId = $parentAction->GetProject();
            } elseif (isset($data['requirementForReceiving'])) {
                $isProduct = true;
                $requirementForReceiving = $data['requirementForReceiving'];
                $this->initReceivableMapper();
                $parentReceivable = $this->receivableMapper->findById($requirementForReceiving);
                $projectId = $parentReceivable->GetProject();
            } else {
                $isProduct = true;
                $projectId = $data['project'];
            }

            if ($isProduct) {
                $actionLabel = $this->view->translate("#Create product");
            } else {
                $parentActionLink = '/projects/action/detail/?id=' . $parentAction->getId();
                $parentActionLink = "<a href='$parentActionLink'>{$parentAction->getTitle()}</a>";
                $actionLabel = sprintf($this->view->translate("#Create action subordinated to %s"), $parentActionLink);
            }

            $projectData = $this->populateProjectFields($projectId, $form);
            $this->populateSupervisorField($form);
            //$this->populateRequirementForReceivingField($projectId, $form, $requirementForReceiving);

            $subordinatedToField = $form->getElement('subordinatedTo');
            $subordinatedToField->setValue($subordinatedTo);

            //$this->populateSubordinatedToField($projectId, $form, 0, $subordinatedTo);
            $pageData = array(
                'projectId'    => $projectId,
                'projectTitle' => $projectData['title'],
                'actionLabel'  => $actionLabel,
                );
            $this->view->pageData = $pageData;
        }
    }

    public function createProductAction()
    {
        // cria form
        $form = new C3op_Form_ProductCreate;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully created.'));
                $this->_redirect(sprintf('/projects/action/detail/?id=%d&success=1', $id));
            } else {
                //form error: populate and go back
                $form->populate($postData);
                $this->view->form = $form;
            }
        } else {
            $data = $this->_request->getParams();
            $requirementForReceiving = 0;
            if (isset($data['requirementForReceiving'])) {
                $requirementForReceiving = $data['requirementForReceiving'];
                $this->initReceivableMapper();
                $parentReceivable = $this->receivableMapper->findById($requirementForReceiving);
                $projectId = $parentReceivable->GetProject();
            } else {
                $projectId = $data['project'];
            }

            $element = $form->getElement('product');
            $element->setValue(1);

            $projectData = $this->populateProjectFields($projectId, $form);
            $this->populateSupervisorField($form);
            $this->populateRequirementForReceivingField($projectId, $form, $requirementForReceiving);
            $pageData = array(
                'id'           => $projectId,
                'projectTitle' => $projectData['title'],
                );
            $this->view->pageData = $pageData;
        }
    }

    public function editAction()
    {
        $form = new C3op_Form_ActionEdit;
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect(sprintf('/projects/action/detail/?id=%d&success=1', $id));
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

                $this->initActionMapper();
                $inputAction = $this->actionMapper->findById($id);
                $element = $form->getElement('title');
                $element->setValue($inputAction->getTitle());

                $element = $form->getElement('id');
                $element->setValue($id);

//                $element = $form->getElement('milestone');
//                $element->setValue($inputAction->getMilestone());

                $element = $form->getElement('product');
                $element->setValue($inputAction->getProduct());

                $projectData = $this->populateProjectFields($inputAction->getProject(), $form);
                $projectId = $projectData['id'];

                $element = $form->getElement('description');
                $element->setValue($inputAction->getDescription());

                $this->setDateValueToFormField($form, 'baselineBeginDate', $inputAction->getBaselineBeginDate());
                $this->setDateValueToFormField($form, 'baselineFinishDate', $inputAction->getBaselineFinishDate());
                $this->setDateValueToFormField($form, 'predictedBeginDate', $inputAction->getPredictedBeginDate());
                $this->setDateValueToFormField($form, 'predictedFinishDate', $inputAction->getPredictedFinishDate());
                $this->setDateValueToFormField($form, 'realBeginDate', $inputAction->getRealBeginDate());
                $this->setDateValueToFormField($form, 'realFinishDate', $inputAction->getRealFinishDate());

                $user = Zend_Registry::get('user');
                $role = $user->GetRole();
                if ($role == C3op_Access_RolesConstants::ROLE_SYSADMIN) {
                    $element = $form->getElement('status');
                    $element->setValue($inputAction->getStatus());
                } else {
                    $form->removeElement('status');
                }

                $this->populateSupervisorField($form, $inputAction->getSupervisor());

                if ($inputAction->getSubordinatedTo() > 0) {
                    //$form->removeElement('requirementForReceiving');
                } else {
                    $this->populateRequirementForReceivingField($projectId, $form, $inputAction->getRequirementForReceiving());
                }

//                $subordinatedToField = $form->getElement('subordinatedTo');
//                $subordinatedToField->setValue($inputAction->getSubordinatedTo());

                $this->populateSubordinatedToField($projectId, $form, $id, $inputAction->getSubordinatedTo());
            }

            $pageData = array(
                'id' => $id,
                'title' => $projectData['title']
            );
            $this->view->pageData = $pageData;
        }
    }

    public function editProductAction()
    {
        $form = new C3op_Form_ProductEdit;
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect(sprintf('/projects/action/detail/?id=%d&success=1', $id));
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

                $this->initActionMapper();
                $inputAction = $this->actionMapper->findById($id);
                $element = $form->getElement('title');
                $element->setValue($inputAction->getTitle());

                $element = $form->getElement('id');
                $element->setValue($id);

                $element = $form->getElement('product');
                $element->setValue($inputAction->getProduct());

                $element = $form->getElement('milestone');
                $element->setValue($inputAction->getMilestone());

                $projectData = $this->populateProjectFields($inputAction->getProject(), $form);
                $projectId = $projectData['id'];

                $element = $form->getElement('description');
                $element->setValue($inputAction->getDescription());

                $this->setDateValueToFormField($form, 'predictedBeginDate', $inputAction->getPredictedBeginDate());
                $this->setDateValueToFormField($form, 'predictedFinishDate', $inputAction->getPredictedFinishDate());

                $user = Zend_Registry::get('user');
                $role = $user->GetRole();
                if ($role == C3op_Access_RolesConstants::ROLE_SYSADMIN) {
                    $element = $form->getElement('status');
                    $element->setValue($inputAction->getStatus());
                } else {
                    $form->removeElement('status');
                }

                $this->populateSupervisorField($form, $inputAction->getSupervisor());
                $this->populateRequirementForReceivingField($projectId, $form, $inputAction->getRequirementForReceiving());
//                $subordinatedToField = $form->getElement('subordinatedTo');
//                $subordinatedToField->setValue($inputAction->getSubordinatedTo());

                $this->populateSubordinatedToField($projectId, $form, $id, $inputAction->getSubordinatedTo());
            }

            $pageData = array(
                'id' => $id,
                'projectId' => $projectData['id'],
                'title' => $projectData['title']
            );
            $this->view->pageData = $pageData;
        }
    }

    public function detailAction()
    {
        $pageData = array();

        $this->initActionMapper();
        $this->initProjectMapper();
        $this->initContactMapper();
        $this->initInstitutionMapper();

        $actionToBeDetailed =  $this->initActionWithCheckedId($this->actionMapper);
//        $projectToBeDetailed = $this->projectMapper->findById($actionToBeDetailed->getProject());
        $messageToShow = $this->_helper->flashMessenger->getMessages();

        $header = new C3op_Projects_ActionHeader($this->db, $actionToBeDetailed, $this->actionMapper);
        $actionHeader = $header->fetch();

        // responsibleList
        //   * responsibleInfo
        //      id
        //      contactId
        //      linkageId
        //      name
        //      description
        //      value
        //      contractingStatusLabel
        //      canContractFlag
        //      canRemoveResponsible
        //      canProvideOutlay

        $responsiblesList = $this->getResponsiblesList($actionToBeDetailed);

        // outsideServiceList
        //   * outsideServiceInfo
        //      id
        //      name
        //      description
        //      value
        //      contractingStatusLabel
        //      canContractFlag
        //      canRemoveOutsideService
        //      canProvideOutlay

        $outsideServicesList = $this->getOutsideServicesList($actionToBeDetailed);

        // materialSupplyList
        //   * materialSupplyInfo
        //      id
        //      name
        //      description
        //      value
        //      contractingStatusLabel
        //      canContractFlag
        //      canRemoveMaterialSupply
        //      canProvideOutlay

        $materialSuppliesList = $this->getMaterialSuppliesList($actionToBeDetailed);

        $pageData = array(
            'messageToShow'       => $messageToShow,
            'actionHeader'        => $actionHeader,
            'responsiblesList'     => $responsiblesList,
            'outsideServicesList' => $outsideServicesList,
            'materialSuppliesList' => $materialSuppliesList,
        );

        $this->view->pageData = $pageData;

    }


    public function removeAction()
    {
        $form = new C3op_Form_ActionRemove();
        $this->view->form = $form;
        $this->initActionMapper();

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $submitButton = $form->getUnfilteredValue('Submit');

                if ($submitButton) {
                    $theAction = $this->actionMapper->findById($postData['id']);
                    $parentActionId = $theAction->getSubordinatedTo();
                    if ($parentActionId) {
                        $redirectTo = '/projects/action/detail/?id=' . $parentActionId;
                    } else {
                        $projectAction = $theAction->getProject();
                        $redirectTo = '/projects/project/detail/?id=' . $projectAction;
                    }

                    $id = $form->process($postData);
                    $this->_helper->getHelper('FlashMessenger')
                        ->addMessage($this->view->translate('#The record was successfully removed.'));
                    $this->_redirect($redirectTo);
                } else {
                    $id = $postData['id'];
                    $this->_redirect('/projects/action/detail/?id=' . $id);
                }
            } else {
                //form error: populate and go back
                $this->view->form = $form;
            }
        } else {
            // GET
            $id = $this->checkIdFromGet();
            $actionData = array();
            $thisAction = $this->actionMapper->findById($id);
            $idField = $form->getElement('id');
            $idField->setValue($id);
            $actionData = array(
                'id'   => $id,
                'title' => $thisAction->getTitle(),
            );
            $this->view->actionData = $actionData;
        }
    }

    public function acknowledgeReceiptAction()
    {
        $this->_helper->layout->disableLayout();

        $user = Zend_Registry::get('user');
        $acl = Zend_Registry::get('acl');

        $tester = new C3op_Access_PrivilegeTester($user, $acl, "projects", "action", "accept-receipt");
        if (!$tester->allow()) {
            $this->_helper->viewRenderer->setNoRender(TRUE);
        }


        $this->initActionMapper();
        $actionToBeChanged =  $this->initActionWithCheckedId($this->actionMapper);

        $acknowledgment = new C3op_Projects_ReceiptAcknowledgment();
        $acknowledgment->AcknowledgeReceipt($actionToBeChanged, $this->actionMapper);

        $actionHeader = array(
            'id' => $actionToBeChanged->getId(),
        );

        $this->view->actionHeader = $actionHeader;


    }

   public function acceptReceiptAction()
    {
        $this->_helper->layout->disableLayout();
        //$this->_helper->viewRenderer->setNoRender(TRUE);

        $this->initActionMapper();
        $actionToBeChanged =  $this->initActionWithCheckedId($this->actionMapper);
        $acceptance = new C3op_Projects_ReceiptAcceptance();
        $acceptance->AcceptReceipt($actionToBeChanged, $this->actionMapper);

    }

   public function rejectReceiptAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $this->initActionMapper();
        $actionToBeChanged =  $this->initActionWithCheckedId($this->actionMapper);
        $rejection = new C3op_Projects_ReceiptRejection();
        $rejection->RejectReceipt($actionToBeChanged, $this->actionMapper);

    }

    public function acknowledgeStartAction()
    {
        $this->_helper->layout->disableLayout();
        //$this->_helper->viewRenderer->setNoRender(TRUE);

        $id = $this->checkIdFromGet();
        $this->initActionMapper();
        $actionToBeChanged =  $this->initActionWithCheckedId($this->actionMapper);

        $acknowledgment = new C3op_Projects_ActionAcknowledgeStart($actionToBeChanged);

        $this->view->pageData = array('id' => $id);


    }

    public function deliveryNotifyAction()
    {
        // cria form
        $form = new C3op_Form_ProductDeliveryNotify;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/projects/action/detail/?id=' . $id);
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
                $this->initActionMapper();
                $productToBeNotified =  $this->initActionWithCheckedId($this->actionMapper);
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $productToBeNotified->getId());
//                $this->setDateValueToFormField($form, 'realDate', $thisAction->GetRealDate());
//                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'realValue', $thisAction->GetRealValue());
                $projectId = $productToBeNotified->GetProject();
                $data = $this->getActionData($productToBeNotified);
                $this->populateProjectFields($projectId, $form);
                $this->view->pageData = $data;
            }

        }
    }



    private function initActionWithCheckedId(C3op_Projects_ActionMapper $mapper)
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
        throw new C3op_Projects_ActionException("Invalid Action Id from Get");

    }

    private function populateProjectFields($projectId, Zend_Form $form)
    {
        $validator = new C3op_Util_ValidId();
        if ($validator->isValid($projectId)) {
            $projectField = $form->getElement('project');
            $projectField->setValue($projectId);
//            $projectField = $form->getElement('product');
//            $projectField->setValue($projectId);
            if (!isset($this->projectMapper)) {
                $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
            }
            $theProject = $this->projectMapper->findById($projectId);
            $this->view->projectTitle = $theProject->GetShortTitle();
            $this->view->linkProjectDetail = "/projects/project/detail/?id=" . $theProject->GetId();

            $data = array(
                'id' => $projectId,
                'title' => $theProject->GetShortTitle(),
            );

            return $data;
        } else throw new C3op_Projects_ActionException(_("#It needs a positive integer project id to populate project fields."));

   }

    private function populateSubordinatedToField($projectId, Zend_Form $form, $actionId = 0, $parentActionId = 0)
    {
        $validator = new C3op_Util_ValidId();
        if ($validator->isValid($projectId)) {
            $subordinatedToField = $form->getElement('subordinatedTo');

            $data = $this->fillSubordinatedToDataFrom($projectId, $actionId);

            while (list($key, $row) = each($data)) {
                $eachAction = $this->actionMapper->findById($row['id']);
                $subordinatedToField->addMultiOption($row['id'], $row['title']);
            }
            $subordinatedToField->setValue($parentActionId);

        } else throw new C3op_Projects_ActionException(_("#It needs a positive integer project id to find other actions from same project."));
   }

    private function populateRequirementForReceivingField($projectId, Zend_Form $form, $setedReceivableId = 0)
    {

        $validator = new C3op_Util_ValidId();
        if ($validator->isValid($projectId)) {
            $requirementForReceivingField = $form->getElement('requirementForReceiving');

            $this->initProjectMapper();
            $this->initReceivableMapper();
            $theProject = $this->projectMapper->findById($projectId);
            $allReceivables = $this->receivableMapper->getAllReceivables($theProject);

            while (list($key, $receivableId) = each($allReceivables)) {
                $eachReceivable = $this->receivableMapper->findById($receivableId);
                $requirementForReceivingField->addMultiOption($receivableId, $eachReceivable->GetTitle());
            }

            $requirementForReceivingField->setValue($setedReceivableId);

        } else throw new C3op_Projects_ActionException("Action needs a positive integer project id to find possible receivables to to be a requirement.");
   }

    private function populateSupervisorField(Zend_Form $form, $currentSupervisor = 0)
    {
            $this->initContactMapper();
            $supervisorField = $form->getElement('supervisor');
            $allThatCanBeSupervisor = $this->contactMapper->getAllContactThatAreLinkedToAContractant("name");
            while (list($key, $contactId) = each($allThatCanBeSupervisor)) {
                $eachPossibleSupervisor = $this->contactMapper->findById($contactId);
                $supervisorField->addMultiOption($contactId, $eachPossibleSupervisor->GetName());
            }
            $supervisorField->setValue($currentSupervisor);
   }

    private function initProjectMapper()
    {
        if (!isset($this->projectMapper)) {
             $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
        }
    }

    private function initActionMapper()
    {
        if (!isset($this->actionMapper)) {
            $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
        }
    }

    private function initContactMapper()
    {
        if (!isset($this->contactMapper)) {
            $this->contactMapper = new C3op_Register_ContactMapper($this->db);
        }
    }

    private function initInstitutionMapper()
    {
        if (!isset($this->institutionMapper)) {
            $this->institutionMapper = new C3op_Register_InstitutionMapper($this->db);
        }
    }

    private function initLinkageMapper()
    {
        if (!isset($this->linkageMapper)) {
            $this->linkageMapper = new C3op_Register_LinkageMapper($this->db);
        }
    }

    private function initResponsibleMapper()
    {
        if (!isset($this->responsibleMapper)) {
            $this->responsibleMapper = new C3op_Resources_ResponsibleMapper($this->db);
        }
    }

    private function initOutsideServiceMapper()
    {
        if (!isset($this->outsideServiceMapper)) {
            $this->outsideServiceMapper = new C3op_Resources_OutsideServiceMapper($this->db);
        }
    }

    private function initMaterialSupplyMapper()
    {
        if (!isset($this->materialSupplyMapper)) {
            $this->materialSupplyMapper = new C3op_Resources_MaterialSupplyMapper($this->db);
        }
    }

    private function initReceivableMapper()
    {
        if (!isset($this->receivableMapper)) {
            $this->receivableMapper = new C3op_Finances_ReceivableMapper($this->db);
        }
    }

    private function calculateTotalValueExistentOutlays(C3op_Resources_Responsible $h)
    {
        if (!isset($this->outlayMapper)) {
            $this->outlayMapper = new C3op_Finances_OutlayMapper($this->db);
        }

        $outlays = $this->outlayMapper->getAllOutlaysForResponsible($h);

        $totalValue = 0;
        foreach ($outlays as $outlayId) {
            $thisOutlay = $this->outlayMapper->findById($outlayId);
            $totalValue += $thisOutlay->GetPredictedValue();
        }

        return $totalValue;
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

     private function getResponsiblesList(C3op_Projects_Action $action)
    {
        // responsibleList
        //   * responsibleInfo
        //      id
        //      contactId
        //      linkageId
        //      name
        //      description
        //      value
        //      contractingStatusLabel
        //      canContractFlag
        //      canRemoveResponsible
        //      canEditResource
        //      canProvideOutlay
        $this->initActionMapper();
        $this->initResponsibleMapper();

        if (!isset($this->linkageMapper)) {
            $this->initLinkageMapper();
        }

        $responsiblesList = array();
        //$responsiblesIdsList = $this->responsibleMapper->getAllResponsiblesOnAction($action);
        $responsiblesIdsList = $this->actionMapper->getAllUniqueResponsiblesContractedOrPredictedUnderAction($action);

        foreach ($responsiblesIdsList as $responsibleId) {
            $theResponsible = $this->responsibleMapper->findById($responsibleId);

            $responsibleAction = $this->actionMapper->findById($theResponsible->getAction());
            $responsibleActionTitle = $responsibleAction->getTitle();

            $contactId = $theResponsible->GetContact();
            $contactName = "(indefinido)";
            if ($contactId > 0) {
                $this->initContactMapper();
                $this->initLinkageMapper();
                $contractedContact = $this->contactMapper->findById($contactId);
                $contactName = $contractedContact->GetName();
            }


            $status = $theResponsible->getStatus();
            $statusTypes = new C3op_Resources_ResponsibleStatusTypes();
            $statusLabel = $this->view->translate($statusTypes->TitleForType($status));

            if ((($theResponsible->getContact() > 0) || ($theResponsible->getInstitution() > 0))
                    && ($status == C3op_Resources_ResponsibleStatusConstants::STATUS_FORESEEN)) {
                $canContract = true;
            } else {
                $canContract = false;
            }

            $canDismiss = false;
            if ($status == C3op_Resources_ResponsibleStatusConstants::STATUS_CONTRACTED) {
                $canDismiss = true;
                $doesIt = new C3op_Resources_ResponsibleHasCredit($theResponsible, $this->responsibleMapper);
                if ($doesIt->hasCreditToProvide()) {
                    $canProvideOutlay = true;
                } else {
                    $canProvideOutlay = false;
                }
            } else {
                $canProvideOutlay = false;
            }

            $removal = new C3op_Resources_ResponsibleRemoval($theResponsible, $this->responsibleMapper);
            if ($removal->canBeRemoved()) {
                $canRemoveResponsible = true;
                $canEditResource = true;
            } else {
                $canRemoveResponsible = false;
                $canEditResource = false;
            }

            $responsiblesList[$responsibleId] = array(
                'id'                     => $responsibleId,
                'contactId'              => $contactId,
                'name'                   => $contactName,
                'responsibleActionId'     => $theResponsible->getAction(),
                'responsibleActionTitle'  => $responsibleActionTitle,
                'contractingStatusLabel' => $statusLabel,
                'canContractFlag'        => $canContract,
                'canDismissFlag'         => $canDismiss,
                'canRemoveResponsible'    => $canRemoveResponsible,
                'canEditResource'        => $canEditResource,
                'canProvideOutlay'       => $canProvideOutlay,

            );
        }

        return $responsiblesList;

    }

    public  function populateSubordinatedToAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $id = $this->checkIdFromGet();
        if (!isset($this->actionMapper)) {
            $this->initActionMapper();
        }
        $action = $this->actionMapper->findById($id);
        $projectId = $action->getProject();

        $data = $this->fillSubordinatedToDataFrom($projectId, $id);


        echo json_encode($data);


    }




     private function getOutsideServicesList(C3op_Projects_Action $action)
    {

        // outsideServiceList
        //   * outsideServiceInfo
        //      id
        //      name
        //      description
        //      value
        //      contractingStatusLabel
        //      canContractFlag
        //      canRemoveOutsideService
        //      canProvideOutlay

        $this->initOutsideServiceMapper();
        if (!isset($this->linkageMapper)) {
            $this->initLinkageMapper();
        }

        $outsideServicesList = array();
        $outsideServicesIdsList = $this->outsideServiceMapper->getAllOutsideServicesOnAction($action);

        foreach ($outsideServicesIdsList as $outsideServiceId) {
            $theOutsideService = $this->outsideServiceMapper->findById($outsideServiceId);

            $outsideServiceAction = $this->actionMapper->findById($theOutsideService->getAction());
            $outsideServiceActionTitle = $outsideServiceAction->getTitle();

            $institutionId = $theOutsideService->GetInstitution();
            $institutionName = $this->view->translate("(#not defined)");
            if ($institutionId > 0) {
                $this->initContactMapper();
                $this->initInstitutionMapper();
                $institutionService = $this->institutionMapper->findById($institutionId);
                $institutionName = $institutionService->GetName();
            }


            $status = $theOutsideService->getStatus();
            $statusTypes = new C3op_Resources_OutsideServiceStatusTypes();
            $statusLabel = $this->view->translate($statusTypes->TitleForType($status));

            if ($status == C3op_Resources_OutsideServiceStatusConstants::STATUS_FORESEEN) {
                $canContract = true;
            } else {
                $canContract = false;
            }

            if ($status == C3op_Resources_OutsideServiceStatusConstants::STATUS_CONTRACTED) {
                $doesIt = new C3op_Resources_OutsideServiceHasCredit($theOutsideService, $this->outsideServiceMapper);
                if ($doesIt->hasCredit()) {
                    $canProvideOutlay = true;
                } else {
                    $canProvideOutlay = false;
                }
            } else {
                $canProvideOutlay = false;
            }
           $removal = new C3op_Resources_OutsideServiceRemoval($theOutsideService, $this->outsideServiceMapper);

            if ($removal->canBeRemoved()) {
                $canRemoveOutsideService = true;
            } else {
                $canRemoveOutsideService = false;
            }




            $outsideServicesList[$outsideServiceId] = array(
                'id'                     => $outsideServiceId,
                'name'                    => $institutionName,
                'outsideServiceActionId'     => $theOutsideService->getAction(),
                'outsideServiceActionTitle'  => $outsideServiceActionTitle,
                'contractingStatusLabel'  => $statusLabel,
                'canContractFlag'         => $canContract,
                'canRemoveOutsideService' => $canRemoveOutsideService,
                'canProvideOutlay'        => $canProvideOutlay,

            );
        }

        return $outsideServicesList;

    }

     private function getMaterialSuppliesList(C3op_Projects_Action $action)
    {
        // materialSupplyList
        //   * materialSupplyInfo
        //      id
        //      name
        //      description
        //      value
        //      contractingStatusLabel
        //      canContractFlag
        //      canRemoveMaterialSupply
        //      canProvideOutlay

        $this->initMaterialSupplyMapper();
        if (!isset($this->linkageMapper)) {
            $this->initLinkageMapper();
        }

        $materialSuppliesList = array();
        $materialSuppliesIdsList = $this->materialSupplyMapper->getAllMaterialSuppliesOnAction($action);

        foreach ($materialSuppliesIdsList as $materialSupplyId) {
            $theMaterialSupply = $this->materialSupplyMapper->findById($materialSupplyId);
            $currencyDisplay = new  C3op_Util_CurrencyDisplay();
            $currencyValue = $currencyDisplay->FormatCurrency($theMaterialSupply->GetTotalValue());
            //$totalValueExistentOutlays = $this->calculateTotalValueExistentOutlays($theMaterialSupply);
            $totalValueExistentOutlays = "???";

            $descriptionMessage = $theMaterialSupply->GetDescription();

            $institutionId = $theMaterialSupply->GetInstitution();
            $institutionName = $this->view->translate("(#not defined)");
            if ($institutionId > 0) {
                $this->initContactMapper();
                $this->initInstitutionMapper();
                $institutionService = $this->institutionMapper->findById($institutionId);
                $institutionName = $institutionService->GetName();
            }


            $status = $theMaterialSupply->getStatus();
            $statusTypes = new C3op_Resources_MaterialSupplyStatusTypes();
            $statusLabel = $this->view->translate($statusTypes->TitleForType($status));

            if ($status == C3op_Resources_MaterialSupplyStatusConstants::STATUS_FORESEEN) {
                $canContract = true;
            } else {
                $canContract = false;
            }

            if ($status == C3op_Resources_MaterialSupplyStatusConstants::STATUS_CONTRACTED) {
                $doesIt = new C3op_Resources_MaterialSupplyHasCredit($theMaterialSupply, $this->materialSupplyMapper);
                if ($doesIt->hasCredit()) {
                    $canProvideOutlay = true;
                } else {
                    $canProvideOutlay = false;
                }
            } else {
                $canProvideOutlay = false;
            }
           $removal = new C3op_Resources_MaterialSupplyRemoval($theMaterialSupply, $this->materialSupplyMapper);

            if ($removal->canBeRemoved()) {
                $canRemoveMaterialSupply = true;
            } else {
                $canRemoveMaterialSupply = false;
            }




            $materialSuppliesList[$materialSupplyId] = array(
                'id'                      => $materialSupplyId,
                'name'                    => $institutionName,
                'description'             => $descriptionMessage,
//                'value'                   => $currencyValue,
                'value'                  => "N/D",
                'contractingStatusLabel'  => $statusLabel,
                'canContractFlag'         => $canContract,
                'canRemoveMaterialSupply' => $canRemoveMaterialSupply,
                'canProvideOutlay'        => $canProvideOutlay,

            );
        }

        return $materialSuppliesList;

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
                    'actionObj' => $actionObj,
                    'projectId' => $projectId,
                    'actionId' => $projectAction,
                );

        return $data;


    }


    private function fillSubordinatedToDataFrom($projectId, $actionId=0)
    {
            if (!isset($this->actionMapper)) {
                $this->initActionMapper();
            }

            if ($actionId > 0) {
                $actionToBePopulated = $this->actionMapper->findById($actionId);
                $parentActionId = $actionToBePopulated->GetSubordinatedTo();
                $actionsList = $this->actionMapper->getPossibleSubordination($actionToBePopulated);
            } else {
                if (!isset($this->projectMapper)) {
                    $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
                }
                $theProject = $this->projectMapper->findById($projectId);
                $actionsList = $this->projectMapper->getAllActions($theProject);
            }

            $data = array();
            foreach ($actionsList as $k => $id) {
                $loopAction = $this->actionMapper->findById($id);
                $data[] = array('id' => $id, 'title' => $loopAction->getTitle());
            }

            return $data;

    }

    private function getActionData(C3op_Projects_Action $action)
    {
        $projectId = $action->getProject();

        $validator = new C3op_Util_ValidId();
        if ($validator->isValid($projectId)) {
            $this->initProjectMapper();
            $thisProject = $this->projectMapper->findById($projectId);
            $currencyDisplay = new  C3op_Util_CurrencyDisplay();
            $predictedDate = C3op_Util_DateDisplay::FormatDateToShow($action->getPredictedFinishDate());
            $actionDetails = sprintf ($this->view->translate("#Finish predicted for %s"), $predictedDate);

            $data = array(
                'title'             => $action->getTitle(),
                'projectTitle'      => $thisProject->GetShortTitle(),
                'projectId'         => $projectId,
                'actionDetails'     => $actionDetails,
            );

            return $data;
        } else throw new C3op_Finances_ActionException("Action needs a positive integer project id.");

    }



}