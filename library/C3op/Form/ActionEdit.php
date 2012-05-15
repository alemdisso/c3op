<?php
class C3op_Form_ActionEdit extends C3op_Form_ActionCreate
{
    public function init()
    {
        parent::init();

        // initialize form
        $this->setName('editActionForm')
            ->setAction('/projects/action/edit')
            ->setMethod('post');
        
        $id = new Zend_Form_Element_Hidden('id');
        $id->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');        
        $this->addElement($id);
        
                        

    }
    
    public function process($data) {
        
        $db = Zend_Registry::get('db');
        $actionMapper = new C3op_Projects_ActionMapper($db);        
        
        if ($this->isValid($data) !== true) {
            throw new C3op_Form_ActionEditException('Invalid data!');
        } else {
            $id = $data['id'];
            $action = $actionMapper->findById($id);      
            $action->SetTitle($data['title']);
            $action->SetProject($data['project']);
            
            $actionMapper->update($action);
        }
    }
 }