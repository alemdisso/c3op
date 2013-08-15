<?php

class Projects_ProductController extends Zend_Controller_Action
{
    private $actionMapper;
    private $responsibleMapper;
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

        $this->view->pageTitle = "";


    }

    public function postDispatch()
    {
        if (isset($this->view->pageTitle)) {
            $trail = new C3op_Util_Breadcrumb();
            $breadcrumb = $trail->add($this->view->pageTitle, $this->getRequest()->getRequestUri());
            $this->_helper->layout()->getView()->headTitle($this->view->pageTitle);
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
                $this->_redirect('/projects/product/budget-forecast/?actionId=' . $actionId);
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
        $form = new C3op_Form_ProductCreate;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully created.'));
                $this->_redirect(sprintf('/projects/product/detail/?id=%d', $id));
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
            $this->view->pageTitle = $this->view->translate("#New product");

        }
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
                $this->_redirect('/projects/product/detail/?id=' . $id);
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

                $this->view->pageTitle = sprintf($this->view->translate("#Notify delivery of %s", $productToBeNotified->getTitle()));

            }

        }
    }

    public function detailAction()
    {
        $pageData = array();
        //$this->getRequest()->getParam('id');

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
            'materialSuppliesList' => $materialSuppliesList,
        );

        $this->view->pageData = $pageData;
        $this->view->pageTitle = $actionToBeDetailed->getTitle();



    }

    public function editAction()
    {
        $form = new C3op_Form_ProductEdit;
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect(sprintf('/projects/product/detail/?id=%d', $id));
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
            $this->view->pageTitle = $this->view->translate("#Edit product");

        }
    }

    public function leanCreateAction()
    {
        // cria form
        $this->_helper->layout->disableLayout();
        $form = new C3op_Form_ProductLeanCreate;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully created.'));
                $this->_redirect(sprintf('/projects/product/detail/?id=%d', $id));
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
            $element = $form->getElement('requirementForReceiving');
            $element->setValue($requirementForReceiving);
            $element = $form->getElement('project');
            $element->setValue($projectId);
            $this->view->form = $form;

            $pageData = array(
                'id'           => $projectId,
                );

            $this->view->pageData = $pageData;
            $this->view->pageTitle = $this->view->translate("#New product");

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


    private function initResponsibleMapper()
    {
        if (!isset($this->responsibleMapper)) {
            $this->responsibleMapper = new C3op_Resources_ResponsibleMapper($this->db);
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

        $responsiblesList = array();
        //$responsiblesIdsList = $this->responsibleMapper->getAllResponsiblesOnAction($action);
        $responsiblesIdsList = $this->actionMapper->getAllUniqueResponsiblesContractedOrPredictedUnderAction($action);

        foreach ($responsiblesIdsList as $responsibleId) {
            $loopResponsible = $this->responsibleMapper->findById($responsibleId);
            $actionToDo = $this->actionMapper->findById($loopResponsible->getAction());

            $fetcher = new C3op_Projects_ActionResponsible($actionToDo, $this->actionMapper, $this->db);
            $responsibleData = $fetcher->fetch();

            $responsibleActionTitle = $actionToDo->getTitle();

            $institutionId = 0;
            $finder = new C3op_Resources_ResponsibleContactInfo($loopResponsible, $this->responsibleMapper, $this->db);
            $contactLabel = $finder->contactName();
            $contactId = $loopResponsible->getContact();
            $responsibleType = $loopResponsible->getType();


            if ($loopResponsible->getType() == C3op_Resources_ResponsibleTypeConstants::TYPE_TEAM_MEMBER) {
                $responsibleLabel = $contactLabel;
                $personal = true;
            } else {
                $finder = new C3op_Resources_ResponsibleInstitutionInfo($loopResponsible, $this->responsibleMapper, $this->db);
                $responsibleLabel = $finder->institutionShortName();
                $institutionId = $loopResponsible->getInstitution();
                if ($contactId > 0) {
                    $responsibleLabel = "$responsibleLabel ($contactLabel)";
                }
                $personal = false;
            }

            $statusLabel = $this->view->translate($responsibleData['responsibleStatusLabel']);
            $canContract = $responsibleData['canContract'];
            $canDismiss = $responsibleData['canDismiss'];
            $canProvideOutlay = $responsibleData['canProvideOutlay'];

            $status = $loopResponsible->getStatus();

            $removal = new C3op_Resources_ResponsibleRemoval($loopResponsible, $this->responsibleMapper);
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
                'institutionId'          => $institutionId,
                'name'                   => $responsibleLabel,
                'responsibleType'        => $responsibleType,
                'personal'               => $personal,
                'responsibleActionId'    => $loopResponsible->getAction(),
                'responsibleActionTitle' => $responsibleActionTitle,
                'contractingStatusLabel' => $statusLabel,
                'canContractFlag'        => $canContract,
                'canDismissFlag'         => $canDismiss,
                'canRemoveResponsible'   => $canRemoveResponsible,
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
            $realDate = C3op_Util_DateDisplay::FormatDateToShow($action->getRealFinishDate());
            $actionDetails = sprintf ($this->view->translate("#Predicted finish: %s Real Finish: %s"), $predictedDate, $realDate);

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