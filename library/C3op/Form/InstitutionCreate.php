<?php
class C3op_Form_InstitutionCreate extends Zend_Form
{
    public function init()
    {

        // initialize form
        $this->setName('newInstitutionForm')
            ->setAction('/register/institution/create')
            ->setMethod('post');
        
        // create text input for name
        $name = new Zend_Form_Element_Text('name');
        $nameValidator = new C3op_Register_InstitutionValidName();
        $name->setLabel('Nome:')
            ->setOptions(array('size' => '50'))
            ->setRequired(true)
            ->addValidator($nameValidator)
//            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim')
                ;
        // attach elements to form
        $this->addElement($name);
        
        // create text input for name
        $shortName = new Zend_Form_Element_Text('shortName');
        $nameValidator = new C3op_Register_InstitutionValidName();
        $shortName->setLabel('Nome curto:')
            ->setOptions(array('size' => '50'))
            ->setRequired(true)
            ->addValidator($nameValidator)
//            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim')
                ;
        // attach elements to form
        $this->addElement($shortName);
        
        $type = new Zend_Form_Element_Select('type');
        $type->setLabel('Tipo')
                ->setRequired(true);
        $titleTypes = C3op_Register_InstitutionTypes::AllTitles();
        $type->addMultiOption(null, "(escolha um tipo)");
        while (list($key, $title) = each($titleTypes)) {
            $type->addMultiOption($key, $title);
        }        
        
        $this->addElement($type);
        
        
        
        // create submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Salvar')
            ->setOptions(array('class' => 'submit'));
        $this->addElement($submit);
                

    }
    
    public function process($data) {
        
        
        if ($this->isValid($data) !== true) 
        {
            throw new C3op_Form_InstitutionCreateException('Invalid data!');
        } 
        else
        {
            $db = Zend_Registry::get('db');
            $institutionMapper = new C3op_Register_InstitutionMapper($db);
            
            $institution = new C3op_Register_Institution();
            $institution->SetName($this->name->GetValue());
            $institution->SetType($this->type->GetValue());
            $institution->SetShortName($this->shortName->GetValue());
            
            $institutionMapper->insert($institution);
        }
    }
 }