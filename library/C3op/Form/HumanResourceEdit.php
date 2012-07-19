<?php
class C3op_Form_HumanResourceEdit extends C3op_Form_HumanResourceCreate
{
    public function init()
    {
        parent::init();

        // initialize form
        $this->setName('editHumanResourceForm')
            ->setAction('/projects/human-resource/edit')
            ->setMethod('post');
        
        $id = new Zend_Form_Element_Hidden('id');
        $id->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');        
        $this->addElement($id);
        
                        

    }
    
    public function process($data) {
        
        $db = Zend_Registry::get('db');
        $humanResourceMapper = new C3op_Projects_HumanResourceMapper($db);        
        
        if ($this->isValid($data) !== true) {
            throw new C3op_Form_InstitutionEditException('Invalid data!');
        } else {
            $id = $data['id'];
            $humanResource = $humanResourceMapper->findById($id);
            $humanResource->SetDescription($this->description->GetValue());
            $humanResource->SetContact($this->contact->GetValue());
            $humanResource->SetValue($this->value->GetValue());
            $humanResource->SetAction($this->action->GetValue());
            $humanResourceMapper->update($humanResource);
            return $humanResource->GetId();
        }
    }
 }