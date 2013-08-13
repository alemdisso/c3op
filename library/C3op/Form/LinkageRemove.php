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
            ->addFilter('StringTrim');
        $this->addElement($contact);

        $institution = new Zend_Form_Element_Hidden('institution');
        $institution->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($institution);

        $id = new Zend_Form_Element_Hidden('id');
        $id->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($id);


        $util = new C3op_Util_SetButtons();
        $util->setButtons($this, array('Submit'=>_('#Confirm removal'), 'Cancel'=>_('#Don\'t remove')));
     }

    public function process($data) {
        if ($this->isValid($data) !== true)
        {
            throw new C3op_Form_LinkageRemoveException(_('#Invalid data!'));
        }
        else
        {
            $db = Zend_Registry::get('db');
            $linkageMapper = new C3op_Register_LinkageMapper($db);
            $linkageToBeRemoved = $linkageMapper->FindById($this->id->GetValue());

            $linkageMapper->delete($linkageToBeRemoved);
        }
    }

}