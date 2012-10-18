<?php
class C3op_Form_TeamMemberEdit extends C3op_Form_TeamMemberCreate
{
    public function init()
    {
        parent::init();

        // initialize form
        $this->setName('editTeamMemberForm')
            ->setAction('/projects/team-member/edit')
            ->setMethod('post');
        
        $id = new Zend_Form_Element_Hidden('id');
        $id->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');        
        $this->addElement($id);
        
                        

    }
    
    public function process($data) {
        
        $db = Zend_Registry::get('db');
        $teamMemberMapper = new C3op_Projects_TeamMemberMapper($db);        
        
        if ($this->isValid($data) !== true) {
            throw new C3op_Form_InstitutionEditException('Invalid data!');
        } else {
            $id = $data['id'];
            $teamMember = $teamMemberMapper->findById($id);
            $teamMember->SetDescription($this->description->GetValue());
            $teamMember->SetContact($this->contact->GetValue());
            $teamMember->SetValue($this->value->GetValue());
            $teamMember->SetAction($this->action->GetValue());
            $teamMemberMapper->update($teamMember);
            return $teamMember->GetId();
        }
    }
 }