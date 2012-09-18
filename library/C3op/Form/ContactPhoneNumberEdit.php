<?php
class C3op_Form_ContactPhoneNumberEdit extends C3op_Form_PhoneNumberEdit
{
    public function __construct($options = null)
    {
        parent::__construct($options);

        $this->setName('newPhoneNumberForm')
            ->setAction('/register/contact/change-phone-number')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        if (isset($options['id'])) {
            $id = new Zend_Form_Element_Hidden('id');
            $id->addValidator('Int')
                ->addFilter('StringTrim');
            $this->addElement($id);
        } else {
            throw  new C3op_Form_PhoneNumberCreateException(_('#Not defined which phone number to edit.'));
        }

        $contact = new Zend_Form_Element_Hidden('contact');
        $contact->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($contact);
    }

    public function process($data) {
        if ($this->isValid($data) !== true)
        {
            throw new C3op_Form_ContactCreateException(_('#Invalid data!'));
        }
        else
        {
            $db = Zend_Registry::get('db');
            $contactMapper = new C3op_Register_ContactMapper($db);
            $contact = $contactMapper->findById($this->contact->GetValue());
            if ($this->localNumber->GetValue() != "") {
                $phoneNumbers = $contact->GetPhoneNumbers();
                if (isset($phoneNumbers[$this->id->GetValue()])) {
                    $phoneNumber = new C3op_Register_ContactPhoneNumber();
                    $phoneNumber->SetId($this->id->GetValue());
                    $phoneNumber->SetAreaCode($this->areaCode->GetValue());
                    $phoneNumber->SetLocalNumber($this->localNumber->GetValue());
                    $phoneNumber->SetLabel($this->label->GetValue());
                    $phoneNumbers[$this->id->GetValue()] = $phoneNumber;
                    $contact->SetPhoneNumbers($phoneNumbers);
                    $contactMapper->update($contact);
                    return $contact->GetId();

                } else {
                    throw new C3op_Form_ContactEditException(_('#Can\'t find this phone id at this contact phone list'));
                }
            }
        }
    }
}