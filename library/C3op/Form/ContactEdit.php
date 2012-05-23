<?php
class C3op_Form_ContactEdit extends C3op_Form_ContactCreate
{
    public function init()
    {
        parent::init();

        // initialize form
        $this->setName('editContactForm')
            ->setAction('/register/contact/edit')
            ->setMethod('post');
        
        $id = new Zend_Form_Element_Hidden('id');
        $id->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');        
        $this->addElement($id);
        
                        

    }
    
    public function process($data) {
        
        $db = Zend_Registry::get('db');
        $contactMapper = new C3op_Register_ContactMapper($db);        
        
        if ($this->isValid($data) !== true) {
            throw new C3op_Form_ContactEditException('Invalid data!');
        } else {
            $id = $data['id'];
            $contact = $contactMapper->findById($id);      
            $contact->SetName($data['name']);
            
            $contact->SetType($data['type']);
            $contactMapper->update($contact);
        }
    }
 }