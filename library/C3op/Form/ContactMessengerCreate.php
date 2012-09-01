<?php
class C3op_Form_ContactMessengerCreate extends C3op_Form_MessengerCreate
{
    public function __construct($options = null)
    {
        parent::__construct($options);

        if (isset($options['contact'])) {
            $contact = new Zend_Form_Element_Hidden('contact');
            $contact->addValidator('Int')
                ->addFilter('StringTrim');
            $this->addElement($contact);
            $this->setAction('/register/contact/add-messenger');
        } else {
            throw  new C3op_Form_MessengerCreateException('Not defined what is this messenger related to.');
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
                $messenger = new C3op_Register_ContactMessenger(0, $this->address->GetValue(), $this->service->GetValue());
                $contact->AddMessenger($messenger);
            }
            $contactMapper->update($contact);
            return $contact->GetId();
        }
    }
}