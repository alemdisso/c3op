<?php
class C3op_Form_LinkagePhoneNumberEdit extends C3op_Form_PhoneNumberEdit
{
    public function __construct($options = null)
    {
        parent::__construct($options);

        $this->setName('newPhoneNumberForm')
            ->setAction('/register/linkage/change-phone-number')
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

        $linkage = new Zend_Form_Element_Hidden('linkage');
        $linkage->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($linkage);
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
            if ($this->localNumber->GetValue() != "") {
                $phoneNumbers = $linkage->GetPhoneNumbers();
                if (isset($phoneNumbers[$this->id->GetValue()])) {
                    $phoneNumber = new C3op_Register_LinkagePhoneNumber();
                    $phoneNumber->SetId($this->id->GetValue());
                    $phoneNumber->SetAreaCode($this->areaCode->GetValue());
                    $phoneNumber->SetLocalNumber($this->localNumber->GetValue());
                    $phoneNumber->SetLabel($this->label->GetValue());
                    $phoneNumbers[$this->id->GetValue()] = $phoneNumber;
                    $linkage->SetPhoneNumbers($phoneNumbers);
                    $linkageMapper->update($linkage);
                    return $linkage->GetId();

                } else {
                    throw new C3op_Form_LinkageEditException(_('#Can\'t find this phone id at this linkage phone list'));
                }
            }
        }
    }
}