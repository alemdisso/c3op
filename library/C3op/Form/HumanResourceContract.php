<?php
class C3op_Form_HumanResourceCreate extends Zend_Form
{
    
    public function init()
    {
        $this->setName('newHumanResourceForm')
            ->setAction('/projects/human-resource/create')
            ->setMethod('post');
        
        $action = new Zend_Form_Element_Hidden('action');
        $action->addValidator('Int')
            ->addFilter('StringTrim');        
        $this->addElement($action);
        
        $this->addElementText('description', 'Atributo:', new C3op_Util_ValidString(), 50);
        $this->addElementText('value', 'Valor:', new C3op_Util_ValidPositiveFloat(), 50);

        $contact = new Zend_Form_Element_Select('contact');
        $contact->setLabel('Quem: ')
                ->setRegisterInArrayValidator(false);
        $contact->addMultiOption(0, "indefinido");
        $this->addElement($contact);
        
        // create submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Salvar')
            ->setOptions(array('class' => 'submit'));
        $this->addElement($submit);
                
    }
    
    public function process($data) {
        if ($this->isValid($data) !== true) 
        {
            throw new C3op_Form_HumanResourceCreateException('Invalid data!');
        } 
        else
        {
            $db = Zend_Registry::get('db');
            $humanResourceMapper = new C3op_Projects_HumanResourceMapper($db);
            $humanResource = new C3op_Projects_HumanResource();
            $humanResource->SetDescription($this->description->GetValue());
            $humanResource->SetContact($this->contact->GetValue());
            $humanResource->SetValue($this->value->GetValue());
            $humanResource->SetAction($this->action->GetValue());
            $humanResourceMapper->insert($humanResource);
            return $humanResource->GetId();
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