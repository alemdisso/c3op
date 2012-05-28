<?php
class C3op_Form_InstitutionEdit extends C3op_Form_InstitutionCreate
{
    public function init()
    {
        parent::init();

        // initialize form
        $this->setName('editInstitutionForm')
            ->setAction('/register/institution/edit')
            ->setMethod('post');
        
        $id = new Zend_Form_Element_Hidden('id');
        $id->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');        
        $this->addElement($id);
        
                        

    }
    
    public function process($data) {
        
        $db = Zend_Registry::get('db');
        $institutionMapper = new C3op_Register_InstitutionMapper($db);        
        
        if ($this->isValid($data) !== true) {
            throw new C3op_Form_InstitutionEditException('Invalid data!');
        } else {
            $id = $data['id'];
            $institution = $institutionMapper->findById($id);      
            $institution->SetName($data['name']);
            $institution->SetShortName($data['shortName']);
            
            $institution->SetType($data['type']);
            $institutionMapper->update($institution);
        }
    }
 }