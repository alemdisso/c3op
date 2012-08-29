<?php
class C3op_Form_LinkagePhoneNumberCreate extends C3op_Form_PhoneNumberCreate
{
    public function __construct($options = null)
    {
        parent::__construct($options);

        if (isset($options['linkage'])) {
            $linkage = new Zend_Form_Element_Hidden('linkage');
            $linkage->addValidator('Int')
                ->addFilter('StringTrim');
            $this->addElement($linkage);
            $this->setAction('/register/linkage/add-phone-number');
        } else {
            throw  new C3op_Form_PhoneNumberCreateException('Not defined what is this phone number related to.');
        }


    }

    public function process($data) {
        if ($this->isValid($data) !== true)
        {
            throw new C3op_Form_LinkageCreateException('Invalid data!');
        }
        else
        {
            $db = Zend_Registry::get('db');
            $linkageMapper = new C3op_Register_LinkageMapper($db);
            $linkage = $linkageMapper->findById($this->linkage->GetValue());
            if ($this->localNumber->GetValue() != "") {
                $phoneNumber = new C3op_Register_LinkagePhoneNumber(0, $this->areaCode->GetValue(), $this->localNumber->GetValue(), $this->label->GetValue());
                $linkage->AddPhoneNumber($phoneNumber);
            }
            $linkageMapper->update($linkage);
            return $linkage->GetId();
        }
    }
}