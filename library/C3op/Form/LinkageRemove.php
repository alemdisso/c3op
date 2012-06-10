<?php
class C3op_Form_LinkageRemove extends Zend_Form
{
    
    public function init()
    {
        $this->setName('removeLinkageForm')
            ->setAction('/register/linkage/remove')
            ->setMethod('post');
        
        $contact = new Zend_Form_Element_Hidden('contact');
        $contact->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');        
        $this->addElement($contact);
        
        $institution = new Zend_Form_Element_Hidden('institution');
        $institution->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');        
        $this->addElement($institution);
        
        $id = new Zend_Form_Element_Hidden('id');
        $id->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');        
        $this->addElement($id);
        

        // create submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Confirmo: remover!')
            ->setOptions(array('class' => 'submit'));
        $this->addElement($submit);
                
    }
    
    public function process($data) {
        if ($this->isValid($data) !== true) 
        {
            throw new C3op_Form_LinkageRemoveException('Invalid data!');
        } 
        else
        {
            $db = Zend_Registry::get('db');
            $linkageMapper = new C3op_Register_LinkageMapper($db);
            $linkageToBeRemoved = $linkageMapper->FindById($this->id->GetValue());
            
            $linkageMapper->delete($linkageToBeRemoved);
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