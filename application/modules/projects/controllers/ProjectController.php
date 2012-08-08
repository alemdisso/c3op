<?php

class Projects_ProjectController extends Zend_Controller_Action
{
    private $projectMapper;
    private $db;
    private $detailProductDepth;
    private $detailProductBrood;
    private $detailProductBreeds;
    private $institutionMapper;
    private $outlayMapper;

    public function init()
    {
        $this->db = Zend_Registry::get('db');
        $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
    }

    public function createAction()
    {
        // cria form
        $form = new C3op_Form_ProjectCreate;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');          
                $this->_redirect('/projects/project/success-create');

            } else throw new C3op_Projects_ProjectException("A project must have a valid title.");
        } else {
            $this->PopulateClientField($form);
            $this->PopulateOurResponsibleField($form);
        }
    }

    public function editAction()
    {
        $form = new C3op_Form_ProjectEdit;
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');          
                $this->_redirect('/projects/project/success-create');
            } else throw new C3op_Projects_ProjectException("A project must have a valid title.");
        } else {
            // GET
            $thisProject = $this->InitProjectWithCheckedId($this->projectMapper);
            $id = $this->checkIdFromGet();
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $id);
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'title', $thisProject->GetTitle());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'client', $thisProject->GetClient());
            $this->PopulateClientField($form, $thisProject->GetClient());
            $this->PopulateOurResponsibleField($form, $thisProject->GetOurResponsible());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'responsibleAtClient', $thisProject->GetResponsibleAtClient());
            $this->SetDateValueToFormField($form, 'beginDate', $thisProject->GetBeginDate());
            $this->SetDateValueToFormField($form, 'finishDate', $thisProject->GetFinishDate());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'value', $thisProject->GetValue());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'status', $thisProject->GetStatus());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'contractNature', $thisProject->GetContractNature());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'areaActivity', $thisProject->GetAreaActivity());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'overhead', $thisProject->GetOverhead());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'managementFee', $thisProject->GetManagementFee());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'object', $thisProject->GetObject());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'summary', $thisProject->GetSummary());
            C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'observation', $thisProject->GetObservation());
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
            $this->getResponse()->setHeader('Refresh', '3; URL=/projects');
        } else {
            $this->_redirect('/projects');    
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
        $actionMapper = new C3op_Projects_ActionMapper($this->db);

        if (!isset($this->projectMapper)) {
            $this->initProjectMapper();
        }
        $projectToBeDetailed = $this->InitProjectWithCheckedId($this->projectMapper);
        
        $linkReceivables = $this->manageReceivablesLink($projectToBeDetailed);
        $linkPayables = $this->managePayablesLink($projectToBeDetailed);
        $linkUnacknowledged = $this->manageUnacknowledgedLink($projectToBeDetailed);
        $linkTree = $this->manageTreeLink($projectToBeDetailed);
        
        $projectProducts = $this->projectMapper->getAllProductsOf($projectToBeDetailed);
        $actionsList = array();
        reset ($actionsList);
        foreach ($projectProducts as $actionId) {
            $this->detailProductBreeds = array();
            $this->detailProductBrood = 0;
            $this->detailProductDepth = 0;
            $thisAction = $actionMapper->findById($actionId);
            $immediateBreed = $actionMapper->getActionsSubordinatedTo($thisAction);
            if (count($immediateBreed) > 0) {
                $broodMessage = count($immediateBreed) . " ações diretamente subordinadas";
                if (count($immediateBreed)== 1) {
                    $broodMessage = count($immediateBreed) . " ação diretamente subordinada";
                }
            } else {
                $broodMessage = "sem ações diretamente subordinadas";
            }
            $actionTitle =  sprintf("<a href=/projects/action/detail/?id=%d>%s</a>", $actionId, $thisAction->GetTitle());
            $specialActionLabel = $this->buildSpecialActionLabel($thisAction);
            $actionsList[$actionId] = array(
                'title' => $actionTitle,
                'depth' => $this->detailProductDepth,
                'brood' => $broodMessage,
                'specialAction' => $specialActionLabel,
                'editLink' => '/projects/action/edit/?id=' . $actionId   ,
            );
        }
        
        $projectInfo = array(
            'title' => $projectToBeDetailed->GetTitle(),
            'editLink' => '/projects/project/edit/?id=' . $projectToBeDetailed->GetId(),
            'linkReceivables' => $linkReceivables,
            'linkPayables' => $linkPayables,
            'linkUnacknowledged' => $linkUnacknowledged,
            'linkTree' => $linkTree,
            'beginDate' => C3op_Util_DateDisplay::FormatDateToShow($projectToBeDetailed->GetBeginDate()),
            'value' => C3op_Util_CurrencyDisplay::FormatCurrency($projectToBeDetailed->GetValue()),
            'linkActionCreate' => '/projects/action/create/?project=' . $projectToBeDetailed->GetId(),
            'actionsList' => $actionsList,
        );

        $this->view->projectInfo = $projectInfo;
    }
    
    public function treeAction()
    {
        $this->initProjectMapper();
        $project =  $this->InitProjectWithCheckedId($this->projectMapper);
        $this->initActionMapper();
        
        $objTree = new C3op_Projects_ProjectTree();
        $tree = $objTree->retrieveTree($project, $this->projectMapper, $this->actionMapper);
        
        $this->treeData = array();
        $this->fillDataTree($tree);
        
        
        $this->view->projectTree = $tree;
        $this->view->treeData = $this->treeData;
        
    }
    
    public function outlaysAction()
    {
        $id = $this->checkIdFromGet();
        $thisProject = $this->projectMapper->findById($id);
        
        $this->initOutlayMapper();
        $list = $this->projectMapper->getAllOutlaysRelatedToDoneActions($thisProject);
        
        $outlaysList = array();
        reset ($list);
        foreach ($list as $id) {
            $thisOutlay = $this->outlayMapper->findById($id);
            
            $humanResourceId = $thisOutlay->GetHumanResource();
            if (!isset($this->humanResourceMapper)) {
                $this->humanResourceMapper = new C3op_Projects_HumanResourceMapper($this->db);
            }
            $outlayHumanResource = $this->humanResourceMapper->findById($humanResourceId);
            $listOutlaysForHumanResource = $this->humanResourceMapper->getAllOutlays($outlayHumanResource);
            $totalParcels = count($listOutlaysForHumanResource);
            
            $parcels = $this->outlayAsAParcel($thisOutlay);
            $description = $outlayHumanResource->GetDescription();
            $contactId = $outlayHumanResource->GetContact();
            if ($contactId) {
                if (!isset($this->contactMapper)) {
                    $this->contactMapper = new C3op_Register_ContactMapper($this->db);
                }
                $outlayContact = $this->contactMapper->findById($contactId);
                $name = $outlayContact->GetName();
            } else {
                $name = "(indefinido)";
            }
            $this->initActionMapper();
            $outlayAction = $this->actionMapper->findById($thisOutlay->GetAction());
            $actionTitle = $outlayAction->GetTitle();
            
            $predictedDate = C3op_Util_DateDisplay::FormatDateToShow($thisOutlay->GetPredictedDate());
            $predictedValue = C3op_Util_CurrencyDisplay::FormatCurrency($thisOutlay->GetPredictedValue());
            

            
            $outlaysList[$id] = array(
                'name'           => $name,
                'description'    => $description,
                'parcels'        => $parcels,
                'actionTitle'    => $actionTitle,
                'predictedDate'  => $predictedDate,
                'predictedValue' => $predictedValue,
            );
        }
        
        $this->view->outlaysList = $outlaysList;
        
    }


    public function receivablesAction()
    {
        $receivableMapper = new C3op_Projects_ReceivableMapper($this->db);

        $id = $this->checkIdFromGet();
        $thisProject = $this->projectMapper->findById($id);
        $receivablesIdList = $this->projectMapper->getAllReceivables($thisProject);
        $receivablesList = array();
        reset ($receivablesList);
        $receivablesTotalValue = 0;
        $receivablesCounter = 0;
        foreach ($receivablesIdList as $receivableId) {
            $thisReceivable = $receivableMapper->findById($receivableId);
            $receivablesCounter++;
            if ($thisReceivable->GetTitle()) {
                $title = $thisReceivable->GetTitle();
            } else {
                $title = "(#$receivablesCounter)";
            }
            
            $validator = new C3op_Util_ValidDate();
            if ($validator->isValid($thisReceivable->GetPredictedDate())) {
                $predictedDate = C3op_Util_DateDisplay::FormatDateToShow($thisReceivable->GetPredictedDate());
            } else {
                $predictedDate = "(data desconhecida)";
            }
            
            if ($thisReceivable->GetPredictedValue() > 0) {
                $receivablesTotalValue += $thisReceivable->GetPredictedValue();
                $predictedValue = C3op_Util_CurrencyDisplay::FormatCurrency($thisReceivable->GetPredictedValue());
            } else {
                $predictedValue = "";
            }
            
            $productsIdList = $receivableMapper->getAllProducts($thisReceivable);
            $productsList = array();
            foreach ($productsIdList as $productId) {
                $actionMapper = new C3op_Projects_ActionMapper($this->db);
                $thisAction = $actionMapper->findById($productId);
                $actionTitle =  sprintf("<a href=/projects/action/detail/?id=%d>%s</a>", $productId, $thisAction->GetTitle());
                $productsList[$productId] = array(
                    'title' => $actionTitle,
                    'linkDetail' => '/projects/action/detail/?id=' . $productId   ,
                );
                
            }

            $receivablesList[$receivableId] = array(
                'title' => $title,
                'productsList' => $productsList,
                'predictedDate' => $predictedDate,
                'predictedValue' => $predictedValue,
                'editLink' => '/projects/receivable/edit/?id=' . $receivableId   ,
            );
        }
        
        if ($receivablesTotalValue == $thisProject->GetValue()) {
            $projectValue = C3op_Util_CurrencyDisplay::FormatCurrency($receivablesTotalValue) . " (OK)";
        } else {
            $projectValue = "Valor do Projeto: " . C3op_Util_CurrencyDisplay::FormatCurrency($thisProject->GetValue());
            $projectValue .= " Total dos recebimentos:" .  C3op_Util_CurrencyDisplay::FormatCurrency($receivablesTotalValue) . " (?)";
            
        }
        
        $projectInfo = array(
            'title' => $thisProject->GetTitle(),
            'linkDetail' => '/projects/project/detail/?id=' . $id   ,
            'projectValue' => $projectValue,
            'editLink' => '/projects/project/edit/?id=' . $id   ,
            'receivablesList' => $receivablesList,
        );

        $this->view->projectInfo = $projectInfo;
    }
    
    public function payablesAction()
    {
        $id = $this->checkIdFromGet();
        $thisProject = $this->projectMapper->findById($id);
        
        $this->initActionMapper();
        $list = $this->projectMapper->getAllDoneActions($thisProject);
        
        $payablesList = array();
        reset ($list);
        foreach ($list as $actionId) {
            $thisAction = $this->actionMapper->findById($actionId);            
            $actionTitle = $thisAction->GetTitle();
            $actionValue = C3op_Util_CurrencyDisplay::FormatCurrency(
                               $this->actionMapper->getContractedValueJustForThisAction($thisAction)
                           );
                        
            $payablesList[$actionId] = array(
                'actionId'       => $actionId,
                'actionTitle'    => $actionTitle,
                'actionValue'    => $actionValue,
            );
        }
        
        $this->view->payablesList = $payablesList;
        
    }

    public function unacknowledgedAction()
    {
        $id = $this->checkIdFromGet();
        $thisProject = $this->projectMapper->findById($id);
        
        $this->initActionMapper();
        $list = $this->projectMapper->getAllUnacknowledgededActions($thisProject, $this->actionMapper);
        
        $unacknowledgededList = array();
        reset ($list);
        foreach ($list as $actionId) {
            $thisAction = $this->actionMapper->findById($actionId);            
            $actionTitle = $thisAction->GetTitle();
            $actionStart = C3op_Util_DateDisplay::FormatDateToShow(
                               $thisAction->GetRealBeginDate()
                           );
                        
            $unacknowledgededList[$actionId] = array(
                'actionId'       => $actionId,
                'actionTitle'    => $actionTitle,
                'actionStart'    => $actionStart,
            );
        }
        
        $this->view->unacknowledgededList = $unacknowledgededList;
        
    }

    private function initProjectMapper()
    {
         $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
    }
    
    private function InitProjectWithCheckedId(C3op_Projects_ProjectMapper $mapper)
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

    private function buildSpecialActionLabel(C3op_Projects_Action $action)
    {
        $isSpecialAction = false;
        $separator = "";
        if ($action->GetMilestone()) {
            $milestone = "*";
            $separator = " ";
            $isSpecialAction = true;
        } else {
            $milestone = "";                
        }

        if ($action->GetRequirementForReceiving()) {
            $isSpecialAction = true;
            $requirementForReceiving = $separator . '$';
        } else {
            $requirementForReceiving = "";  
        }

        if ($isSpecialAction) {
            $specialAction = "($milestone$requirementForReceiving)";
        } else {
            $specialAction = "";
        }

        return $specialAction;
    }
    
    private function manageReceivablesLink(C3op_Projects_Project $project)
    {
        $receivablesIdList = $this->projectMapper->getAllReceivables($project);
        if (count($receivablesIdList) > 0) {
            $linkReceivables = '/projects/project/receivables/?id=' . $project->GetId();
        } else {
            $linkReceivables = "";
        }
        return $linkReceivables;
       
    }

    private function managePayablesLink(C3op_Projects_Project $project)
    {
        $payablesLink = $this->projectMapper->GetAllDoneActions($project);
        if (count($payablesLink) > 0) {
            $linkReceivables = '/projects/project/payables/?id=' . $project->GetId();
        } else {
            $linkReceivables = "";
        }
        return $linkReceivables;
       
    }

    private function manageUnacknowledgedLink(C3op_Projects_Project $project)
    {
        $linkUnacknowledged = '/projects/project/unacknowledged/?id=' . $project->GetId();
        return $linkUnacknowledged;
       
    }

    private function manageTreeLink(C3op_Projects_Project $project)
    {
        $linkTree = '/projects/project/tree/?id=' . $project->GetId();
        return $linkTree;
       
    }

    private function setDateValueToFormField(C3op_Form_ProjectCreate $form, $fieldName, $value)
    {
        $field = $form->getElement($fieldName);
        if ($value != '0000-00-00')  {
            $field->setValue($this->formatDataToShow($value));
        } else {
            $field->setValue("");
        }
    }

    private function formatDataToShow($rawData)
    {
        $dateArray = explode("-", $rawData);
        $formatedDate = $dateArray[2] . '/' . $dateArray[1] . '/' . $dateArray[0]; 
        return $formatedDate;
    }
    
    private function PopulateClientField(Zend_Form $form, $currentClient=0)
    {
        
        if (!isset($this->institutionMapper)) {
            $this->institutionMapper = new C3op_Register_InstitutionMapper($this->db);
        }
        $clientField = $form->getElement('client');
        $allPossibleClients = $this->institutionMapper->getAllPossibleClients();
        while (list($key, $institutionId) = each($allPossibleClients)) {
            $eachPossibleClient = $this->institutionMapper->findById($institutionId);
            $clientField->addMultiOption($institutionId, $eachPossibleClient->GetName());
        }      
        $clientField->setValue($currentClient);
    }

    private function PopulateOurResponsibleField(Zend_Form $form, $currentResponsible = 0)
    {
            if (!isset($this->contactMapper)) {
                $this->contactMapper = new C3op_Register_ContactMapper($this->db);
            }
            $ourResponsibleField = $form->getElement('ourResponsible');
            $allThatCanBeOurResponsible = $this->contactMapper->getAllContactThatAreLinkedToAContractant();
            while (list($key, $contactId) = each($allThatCanBeOurResponsible)) {
                $eachPossibleResponsible = $this->contactMapper->findById($contactId);
                $ourResponsibleField->addMultiOption($contactId, $eachPossibleResponsible->GetName());
            }      
            $ourResponsibleField->setValue($currentResponsible);
   }
    
    private function initOutlayMapper()
    {
        if (!isset($this->outlayMapper)) {
            $this->outlayMapper = new C3op_Projects_OutlayMapper($this->db);
        }        
    }
  
    
    private function initActionMapper()
    {
        if (!isset($this->actionMapper)) {
            $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
        }        
    }

    private function outlayAsAParcel(C3op_Projects_Outlay $outlay)
    {
        $humanResourceId = $outlay->GetHumanResource();
        if (!isset($this->humanResourceMapper)) {
            $this->humanResourceMapper = new C3op_Projects_HumanResourceMapper($this->db);
        }
        $outlayHumanResource = $this->humanResourceMapper->findById($humanResourceId);
        $listOutlaysForHumanResource = $this->humanResourceMapper->getAllOutlays($outlayHumanResource);
        $totalParcels = count($listOutlaysForHumanResource);
        
        $parcelsCount = 0;
        foreach($listOutlaysForHumanResource as $parcelId) {
            $thisParcel = $this->outlayMapper->FindById($parcelId);
            $parcelsCount++;
            if ($thisParcel->GetId() == $outlay->GetId()) {
                $myParcel = $parcelsCount;
            }
        }
        return "$myParcel/$totalParcels";
    }

   private function FillDataTree($tree)
    {
        $this->initActionMapper();
        foreach ($tree as $id => $subTree) {
            $loopAction = $this->actionMapper->findById($id);
            $data = array();
            $data["title"] = $loopAction->GetTitle();
            
            $contract = new C3op_Projects_ActionContracting($loopAction, $this->actionMapper);
            if ($contract->isContracted()) {
                $data["contracted"] = "contratada";                
            } else {
                $data["contracted"] = "";                
            }

            $data["value"] = C3op_Util_CurrencyDisplay::FormatCurrency(
                    $this->actionMapper->getContractedValueForActionTree($loopAction));
            
            $done = new C3op_Projects_ActionDone($loopAction);
            if ($done->isDone()) {
                $data["done"] = "finalizada";
            } else {
                $data["done"] = "";
            }
            
            $this->treeData[$id] = $data;
            
            
            $this->FillDataTree($subTree);
        }
    }
     
}