<?php

class Projects_IndexController extends Zend_Controller_Action
{
    private $db;
    private $projectMapper;
    public function init()
    {
       
        $this->db = Zend_Registry::get('db');
        $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
        
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('list', 'html')
                    ->addActionContext('modify', 'html')
                    ->initContext();
        
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
                'editLink' => '/projects/project/edit/?id=' . $id   ,
                'beginDate' => C3op_Util_DateDisplay::FormatDateToShow($thisProject->GetBeginDate()),
                'value' => C3op_Util_CurrencyDisplay::FormatCurrency($thisProject->GetValue()),
                'linkActionCreate' => '/projects/action/create/?project=' . $id,
                'linkProjectDetail' => '/projects/project/detail/?id=' . $id,
                'linkReceivableCreate' => '/projects/receivable/create/?project=' . $id,
                'linkProjectReceivables' => '/projects/project/receivables/?id=' . $id,
                'actionsCount' => $actionsCount,
            );
        }
        
        $this->view->projectsList = $projectsList;
        
        $this->view->createProjectLink = "/projects/project/create";
        
 
    }

public function listAction() {
    // pretend this is a sophisticated database query
    $data = array('red','green','blue','yellow');
    $this->view->data = $data;
}	
    
}