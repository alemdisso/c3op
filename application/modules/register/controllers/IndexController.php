<?php

class Register_IndexController extends Zend_Controller_Action
{
    private $db;
    private $contactMapper;
    
    public function init()
    {
       
        $this->db = Zend_Registry::get('db');
        $this->contactMapper = new C3op_Register_ContactMapper($this->db);        
        
   }

    public function indexAction()
    {
//        $form = new C3op_Form_Decorators;
//        $this->view->form = $form;

        $list = $this->contactMapper->getAllIds();
        $contactsList = array();
        reset ($list);
        foreach ($list as $id) {
            $thisContact = $this->contactMapper->findById($id);
            
            $contactsList[$id] = array(
                'name' => $thisContact->GetName(),
                'editLink' => '/register/contact/edit/?id=' . $id   ,
                'type' => C3op_Register_ContactTypes::TitleForType($thisContact->GetType()),
            );
        }
        
        $this->view->contactsList = $contactsList;
        
        $this->view->createContactLink = "/register/contact/create";
        
 
    }

	
    
}