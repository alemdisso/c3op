<?php

class Projects_IndexController extends Zend_Controller_Action
{
    private $db;
    private $projectMapper;
    
    public function init()
    {
       
        $this->db = Zend_Registry::get('db');
        $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);        
        
   }

    public function indexAction()
    {

        $list = $this->projectMapper->getAllIds();
        $projectsList = array();
        reset ($list);
        foreach ($list as $id) {
            $thisProject = $this->projectMapper->findById($id);

            $actionsCount = count($this->projectMapper->GetAllActions($thisProject));
            
            $projectsList[$id] = array(
                'title' => $thisProject->GetTitle(),
                'linkEdit' => '/projects/project/edit/?id=' . $id   ,
                'dateBegin' => $thisProject->GetDateBegin(),
                'value' => $thisProject->GetValue(),
                'linkActionCreate' => '/projects/action/create/?project=' . $id,
                'linkProjectDetail' => '/projects/project/detail/?id=' . $id,
                'linkReceivingCreate' => '/projects/receiving/create/?project=' . $id,
                'linkProjectReceivings' => '/projects/project/receivings/?id=' . $id,
                'actionsCount' => $actionsCount,
            );
        }
        
        $this->view->projectsList = $projectsList;
        
        $this->view->createProjectLink = "/projects/project/create";
        
 
    }

	
    
}