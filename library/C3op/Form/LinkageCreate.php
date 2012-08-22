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
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
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
                ->setDecorators(array(
                      'ViewHelper',
                      'Errors',
                      array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                      array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                  ))
                ->setOptions(array('class' => 'eleven columns alpha omega'))
                ->setRegisterInArrayValidator(false);
            $institution->addMultiOption(0, "(escolha uma...)");
            $this->addElement($institution);
        }
        
        // $this->addElementText('position', 'Cargo/Função:', new C3op_Util_ValidString(), 50);

        $shortName = new Zend_Form_Element_Text('position');
        $validator = new C3op_Register_InstitutionValidName;
        $shortName->setLabel('Cargo/Função:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'eleven columns alpha omega'))
            ->setRequired(true)
            ->addValidator($validator)
//            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim')
                ;
        $this->addElement($shortName);

        // $this->addElementText('department', 'Departamento:', new C3op_Util_ValidString(), 50);

        $shortName = new Zend_Form_Element_Text('department');
        $validator = new C3op_Register_InstitutionValidName;
        $shortName->setLabel('Departamento:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'eleven columns alpha omega'))
            ->setRequired(true)
            ->addValidator($validator)
//            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim')
                ;
        $this->addElement($shortName);


        // create submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Gravar')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'five columns inset-by-six omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Invisible')),
              ))
            ->setOptions(array('class' => 'submit two columns alpha omega'));
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
            // $linkage->SetState($this->state->GetValue());
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