<?php
class C3op_Form_LinkageEmailCreate extends C3op_Form_EmailCreate
{
    public function __construct($options = null)
    {
        parent::__construct($options);

        if (isset($options['linkage'])) {
            $linkage = new Zend_Form_Element_Hidden('linkage');
            $linkage->addValidator('Int')
                ->addFilter('StringTrim');
            $this->addElement($linkage);
            $this->setAction('/register/linkage/add-email');
        } else {
            throw  new C3op_Form_EmailCreateException(_('#Not defined what is this email related to.'));
        }


    }

    public function process($data) {
        if ($this->isValid($data) !== true)
        {
            throw new C3op_Form_LinkageCreateException(_('#Invalid data!'));
        }
        else
        {
            $db = Zend_Registry::get('db');
            $linkageMapper = new C3op_Register_LinkageMapper($db);
            $linkage = $linkageMapper->findById($this->linkage->GetValue());
            if ($this->address->GetValue() != "") {
                $email = new C3op_Register_LinkageEmail(0, $this->address->GetValue(), $this->label->GetValue());
                $linkage->AddEmail($email);
            }
            $linkageMapper->update($linkage);
            return $linkage->GetId();
        }
    }
}