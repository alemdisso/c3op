<?php
class C3op_Form_ContactEmailCreate extends C3op_Form_EmailCreate
{
    public function __construct($options = null)
    {
        parent::__construct($options);

        if (isset($options['contact'])) {
            $contact = new Zend_Form_Element_Hidden('contact');
            $contact->addValidator('Int')
                ->addFilter('StringTrim');
            $this->addElement($contact);
            $this->setAction('/register/contact/add-email');
        } else {
            throw  new C3op_Form_EmailCreateException(_('#Not defined what is this email related to.'));
        }


    }

    public function process($data) {
        if ($this->isValid($data) !== true)
        {
            throw new C3op_Form_ContactCreateException('Invalid data!');
        }
        else
        {
            $db = Zend_Registry::get('db');
            $contactMapper = new C3op_Register_ContactMapper($db);
            $contact = $contactMapper->findById($this->contact->GetValue());
            if ($this->address->GetValue() != "") {
                $email = new C3op_Register_ContactEmail(0, $this->address->GetValue(), $this->label->GetValue());
                $contact->AddEmail($email);
            }
            $contactMapper->update($contact);
            return $contact->GetId();
        }
    }
}