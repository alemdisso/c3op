<?php

class Projects_IndexController extends Zend_Controller_Action
{

    private $projectMapper;
    
    public function init()
    {
       
        $db = Zend_Registry::get('db');
        $this->projectMapper = new C3op_Projects_ProjectMapper($db);        
        
   }

    public function indexAction()
    {
        $list = $this->projectMapper->getAllIds();
        $projectsList = array();
        reset ($list);
        foreach ($list as $id) {
            $thisProject = $this->projectMapper->findById($id);
            
            $projectsList[$id] = array(
                'title' => $thisProject->GetTitle(),
                'linkEdit' => '/projects/project/edit/?id=' . $id   ,
                'dateBegin' => $thisProject->GetDateBegin(),
                'value' => $thisProject->GetValue(),
                'linkActionCreate' => '/projects/action/create/?project=' . $id   ,
                
            );
        }
        
        $this->view->projectsList = $projectsList;
        
        $this->view->createProjectLink = "/projects/project/create";
        
 
    }

	
    
}