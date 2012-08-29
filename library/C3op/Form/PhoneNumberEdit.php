<?php
class C3op_Form_PhoneNumberEdit extends C3op_Form_PhoneNumberCreate
{
    public function init()
    {
        parent::init();
        $this->setName('newPhoneNumberForm')
            ->setAction('/register/contact/change-phone-number')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        $id = new Zend_Form_Element_Hidden('id');
        $id->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($id);


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
                $phoneNumbers = $contact->GetPhoneNumbers();
                print_r($phoneNumbers);
                print("<BR>{$this->id->GetValue()}<BR>");
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
                    throw new C3op_Form_ContactEditException('Can\'t find this phone id at this contact phone list');
                }
            }


        }
    }
}