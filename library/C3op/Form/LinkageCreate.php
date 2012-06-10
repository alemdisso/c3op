<?php
class C3op_Form_LinkageCreate extends Zend_Form
{
    
    public function __construct($options = null)
    {
        parent::__construct($options);
        //$this->setAction($options['action'])->setMethod('post');
        // initialize form
        
        $this->setName('newLinkageForm')
            ->setAction('/register/linkage/create')
            ->setMethod('post');
        
        if (isset($options['contact'])) {
            $contact = new Zend_Form_Element_Hidden('contact');
            $contact->addValidator('Int')
                //->addFilter('HtmlEntities')
                ->addFilter('StringTrim');        
            $this->addElement($contact);
        }
        
        if (isset($options['institution'])) {
            $institution = new Zend_Form_Element_Hidden('institution');
            $institution->addValidator('Int')
                //->addFilter('HtmlEntities')
                ->addFilter('StringTrim');        
            $this->addElement($institution);
        } else {
            $institution = new Zend_Form_Element_Select('institution');
            $institution->setLabel('Instituição: ')
                    ->setRegisterInArrayValidator(false);
            $institution->addMultiOption(0, "(escolha uma...)");
            $this->addElement($institution);
        }
        
        $this->addElementText('position', 'Cargo/Função:', new C3op_Util_ValidString(), 50);
        $this->addElementText('department', 'Departamento:', new C3op_Util_ValidString(), 50);
        $this->addElementText('state', 'UF:', new C3op_Register_ValidState(), 30);

        // create submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Salvar')
            ->setOptions(array('class' => 'submit'));
        $this->addElement($submit);
                
    }
    
    public function init()
    {


    }
    
    public function process($data) {
        if ($this->isValid($data) !== true) 
        {
            throw new C3op_Form_LinkageCreateException('Invalid data!');
        } 
        else
        {
            $db = Zend_Registry::get('db');
            $linkageMapper = new C3op_Register_LinkageMapper($db);
            
            $linkage = new C3op_Register_Linkage();
            $linkage->SetDepartment($this->department->GetValue());
            $linkage->SetPosition($this->position->GetValue());
            $linkage->SetState($this->state->GetValue());
            $linkage->SetContact($this->contact->GetValue());
            
            $institution = $this->institution->GetValue();
            $linkage->SetInstitution($institution);
            
            $linkageMapper->insert($linkage);
        }
    }
    
    private function addElementText($fieldName, $label, $validator, $fieldSize)
    {
        $elementText = new Zend_Form_Element_Text($fieldName);
        $elementText->setLabel($label)
            ->setOptions(array('size' => "$fieldSize"))
            ->addValidator($validator)
            ->addFilter('StringTrim')
                ;
        $this->addElement($elementText);
        
    }
    
    
}