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
            $titleField = $form->getElement('title');
            $titleField->setValue($thisProject->getTitle());
            $idField = $form->getElement('id');
            $idField->setValue($id);
            $dateBeginField = $form->getElement('dateBegin');
            $dateBeginValue = $thisProject->GetDateBegin();
            if ($dateBeginValue != '0000-00-00')  {
                $dateArray = explode("-", $dateBeginValue);
                $formatedDate = $dateArray[2] . '/' . $dateArray[1] . '/' . $dateArray[0]; 
                $dateBeginField->setValue($formatedDate);
            } else {
                $dateBeginField->setValue("");
            }
            $valueField = $form->getElement('value');
            $valueField->setValue($thisProject->getValue());
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
            //print_r($actionsBelow);die();
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
        $actionMapper = new C3op_Projects_ActionMapper($this->db);

        $id = $this->checkIdFromGet();
        $thisProject = $this->projectMapper->findById($id);
        $productsIdList = $this->projectMapper->getAllProducts($thisProject);
        $productsList = array();
        reset ($productsList);
        foreach ($productsIdList as $actionId) {
            $thisAction = $actionMapper->findById($actionId);

            $productsList[$actionId] = array(
                'title' => $thisAction->GetTitle(),
                'linkEdit' => '/projects/action/edit/?id=' . $actionId   ,
            );
        }
        $projectInfo = array(
            'title' => $thisProject->GetTitle(),
            'linkEdit' => '/projects/project/edit/?id=' . $id   ,
            'productsList' => $productsList,
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
        $productsIdList = $this->projectMapper->getAllProducts($project);
        if (count($productsIdList) > 0) {
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
    
}