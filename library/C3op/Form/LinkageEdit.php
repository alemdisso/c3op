<?php
class C3op_Form_LinkageEdit extends C3op_Form_LinkageCreate
{
    public function __construct($options = null)
    {
        parent::__construct($options);
        //$this->setAction($options['action'])->setMethod('post');
        // initialize form
        
        $this->setName('editLinkageForm')
            ->setAction('/register/linkage/edit')
            ->setMethod('post');
        
        $id = new Zend_Form_Element_Hidden('id');
        $id->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');        
        $this->addElement($id);
        
        $contact = new Zend_Form_Element_Hidden('contact');
        $contact->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');        
        $this->addElement($contact);
        
        $institution = new Zend_Form_Element_Select('institution');
        $institution->setLabel('Instituição: ')
                ->setRegisterInArrayValidator(false);
        $institution->addMultiOption(0, "(escolha uma...)");
        $this->addElement($institution);
        
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
        parent::init();

        // initialize form
        $this->setName('editLinkageForm')
            ->setAction('/register/linkage/edit')
            ->setMethod('post');
        
        $id = new Zend_Form_Element_Hidden('id');
        $id->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');        
        $this->addElement($id);
        
                        

    }
    
    public function process($data) {
        
        $db = Zend_Registry::get('db');
        $linkageMapper = new C3op_Register_LinkageMapper($db);        
        if ($this->isValid($data) !== true) {
            throw new C3op_Form_LinkageEditException('Invalid data!');
        } else {
            $id = $data['id'];
            $linkage = $linkageMapper->findById($id);      
            $linkage->SetDepartment($data['department']);
            $linkage->SetContact($data['contact']);
            $linkage->SetState($data['state']);
            $linkage->SetInstitution($data['institution']);
            $linkage->SetPosition($data['position']);

            $linkageMapper->update($linkage);
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