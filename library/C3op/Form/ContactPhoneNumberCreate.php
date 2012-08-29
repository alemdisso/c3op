<?php
class C3op_Form_ContactPhoneNumberCreate extends C3op_Form_PhoneNumberCreate
{
    public function __construct($options = null)
    {
        parent::__construct($options);

        if (isset($options['contact'])) {
            $contact = new Zend_Form_Element_Hidden('contact');
            $contact->addValidator('Int')
                ->addFilter('StringTrim');
            $this->addElement($contact);
            $this->setAction('/register/contact/add-phone-number');
        } else {
            throw  new C3op_Form_PhoneNumberCreateException('Not defined what is this phone number related to.');
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
            if ($this->localNumber->GetValue() != "") {
                $phoneNumber = new C3op_Register_ContactPhoneNumber(0, $this->areaCode->GetValue(), $this->localNumber->GetValue(), $this->label->GetValue());
                $contact->AddPhoneNumber($phoneNumber);
            }
            $contactMapper->update($contact);
            return $contact->GetId();
        }
    }
}