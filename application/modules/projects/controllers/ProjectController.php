<?php

class Projects_ProjectController extends Zend_Controller_Action
{
    private $projectMapper;
    private $db;

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
            $this->SetValueToFormField($form, 'id', $id);
            $this->SetValueToFormField($form, 'title', $thisProject->GetTitle());
            $this->SetValueToFormField($form, 'ourResponsible', $thisProject->GetOurResponsible());
            $this->SetValueToFormField($form, 'responsibleAtClient', $thisProject->GetResponsibleAtClient());
            $this->SetDateValueToFormField($form, 'dateBegin', $thisProject->GetDateBegin());
            $this->SetDateValueToFormField($form, 'dateFinish', $thisProject->GetDateFinish());
            $this->SetValueToFormField($form, 'value', $thisProject->GetValue());
            $this->SetValueToFormField($form, 'status', $thisProject->GetStatus());
            $this->SetValueToFormField($form, 'contractNature', $thisProject->GetContractNature());
            $this->SetValueToFormField($form, 'areaActivity', $thisProject->GetAreaActivity());
            $this->SetValueToFormField($form, 'overhead', $thisProject->GetOverhead());
            $this->SetValueToFormField($form, 'managementFee', $thisProject->GetManagementFee());
            $this->SetValueToFormField($form, 'object', $thisProject->GetObject());
            $this->SetValueToFormField($form, 'summary', $thisProject->GetSummary());
            $this->SetValueToFormField($form, 'observation', $thisProject->GetObservation());
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
        
        $linkReceivings = $this->manageReceivingsLink($projectToBeDetailed);
        
        $mainActions = $this->projectMapper->getAllActionsSubordinatedTo($projectToBeDetailed);
        $actionsList = array();
        reset ($actionsList);
        foreach ($mainActions as $actionId) {
            $thisAction = $actionMapper->findById($actionId);
            
            $specialActionLabel = $this->buildSpecialActionLabel($thisAction);

            $actionsList[$actionId] = array(
                'title' => $thisAction->GetTitle(),
                'specialAction' => $specialActionLabel,
                'linkEdit' => '/projects/action/edit/?id=' . $actionId   ,
            );
        
            $manager = new C3op_Projects_SubordinationManager();
            
            $actionsBelow = $manager->GetAllActionsSubordinatedTo($thisAction, $actionMapper, $projectToBeDetailed, $this->projectMapper);
            $this->extractActionsBelow($actionsBelow, $actionMapper);
            
//            foreach ($actionsBelow as $actionTree) {
//                $actionsList[$actionId] = array(
//                    'title' => $thisAction->GetTitle(),
//                    'specialAction' => $specialActionLabel,
//                    'requirementForReceiving' => $requirementForReceiving,
//                    'linkEdit' => '/projects/action/edit/?id=' . $actionId   ,
//                );
//                
//            }
            
        }
        
//        $actionsIdsList = $this->projectMapper->getAllActions($projectToBeDetailed);
//        $actionsList = array();
//        reset ($actionsList);
//        foreach ($actionsIdsList as $actionId) {
//            $thisAction = $actionMapper->findById($actionId);
//            
//            $specialActionLabel = $this->buildSpecialActionLabel($thisAction);
//
//            $actionsList[$actionId] = array(
//                'title' => $thisAction->GetTitle(),
//                'specialAction' => $specialActionLabel,
//                'requirementForReceiving' => $requirementForReceiving,
//                'linkEdit' => '/projects/action/edit/?id=' . $actionId   ,
//            );
//        }
        $projectInfo = array(
            'title' => $projectToBeDetailed->GetTitle(),
            'linkEdit' => '/projects/project/edit/?id=' . $projectToBeDetailed->GetId(),
            'linkReceivings' => $linkReceivings,
            'dateBegin' => $projectToBeDetailed->GetDateBegin(),
            'value' => $projectToBeDetailed->GetValue(),
            'linkActionCreate' => '/projects/action/create/?project=' . $projectToBeDetailed->GetId(),
            'actionsList' => $actionsList,
        );

        $this->view->projectInfo = $projectInfo;
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

    public function receivingsAction()
    {
        $receivingMapper = new C3op_Projects_ReceivingMapper($this->db);

        $id = $this->checkIdFromGet();
        $thisProject = $this->projectMapper->findById($id);
        $receivingsIdList = $this->projectMapper->getAllReceivings($thisProject);
        $receivingsList = array();
        reset ($receivingsList);
        $receivingsTotalValue = 0;
        $receivingsCounter = 0;
        foreach ($receivingsIdList as $receivingId) {
            $thisReceiving = $receivingMapper->findById($receivingId);
            $receivingsCounter++;
            if ($thisReceiving->GetTitle()) {
                $title = $thisReceiving->GetTitle();
            } else {
                $title = "(#$receivingsCounter)";
            }
            
            $validator = new C3op_Util_ValidDate();
            if ($validator->isValid($thisReceiving->GetPredictedDate())) {
                $predictedDate = $this->formatDataToShow($thisReceiving->GetPredictedDate());
            } else {
                $predictedDate = "(data desconhecida)";
            }
            
            if ($thisReceiving->GetPredictedValue() > 0) {
                $receivingsTotalValue += $thisReceiving->GetPredictedValue();
                $predictedValue = "R$ " . $thisReceiving->GetPredictedValue();
            } else {
                $predictedValue = "";
            }
            
            $receivingsList[$receivingId] = array(
                'title' => $title,
                'predictedDate' => $predictedDate,
                'predictedValue' => $predictedValue,
                'linkEdit' => '/projects/receiving/edit/?id=' . $receivingId   ,
            );
        }
        
        if ($receivingsTotalValue == $thisProject->GetValue()) {
            $projectValue = "$receivingsTotalValue (OK)";
        } else {
            $projectValue = "Valor do Projeto: " . $thisProject->GetValue() . " Total dos recebimentos: $receivingsTotalValue (?)";
            
        }
        
        $projectInfo = array(
            'title' => $thisProject->GetTitle(),
            'linkDetail' => '/projects/project/detail/?id=' . $id   ,
            'projectValue' => $projectValue,
            'linkEdit' => '/projects/project/edit/?id=' . $id   ,
            'receivingsList' => $receivingsList,
        );

        $this->view->projectInfo = $projectInfo;
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
    
    private function manageReceivingsLink(C3op_Projects_Project $project)
    {
        $receivingsIdList = $this->projectMapper->getAllReceivings($project);
        if (count($receivingsIdList) > 0) {
            $linkReceivings = '/projects/project/receivings/?id=' . $project->GetId();
        } else {
            $linkReceivings = "";
        }
        return $linkReceivings;
       
    }

    private function extractActionsBelow($actionsBelow, C3op_Projects_ActionMapper $mapper)
    {
        print_r($actionsBelow);
        foreach ($actionsBelow as $childAction) {
            if (isset($childAction['action'])) {
                $action = $childAction['action'];
                $actionsArray = $childAction['actionsBelow'];
                print ($action->GetTitle() . "<br/>");
                if (count($actionsArray)) {
                    $this->extractActionsBelow($childAction, $mapper);
                }
            }
        }
        
    }
 
    private function setValueToFormField(C3op_Form_ProjectCreate $form, $fieldName, $value)
    {
        $field = $form->getElement($fieldName);
        $field->setValue($value);
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
}