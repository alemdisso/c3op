<?php

class C3op_Register_Contact {

    protected $id;
    protected $name;
    protected $type;
    protected $phoneNumbers;

    function __construct($id=0) {
        $this->id = (int)$id;
        $this->name = "";
        $this->phoneNumbers = array();
    }

    public function GetId() {
        return $this->id;

    } //GetId

    public function SetId($id) {
        if (($this->id == 0) && ($id > 0)) {
            $this->id = (int)$id;
        } else {
            throw new C3op_Register_ContactException('It\'s not possible to change a contact\'s ID');
        }

    } //SetId

    public function GetName() {
        return $this->name;

    } //GetName

    public function SetName($name) {
        $validator = new C3op_Register_ContactValidName();
        if ($validator->isValid($name)) {
            if ($this->name != $name) {
                $this->name = $name;
            }
        } else {
            throw new C3op_Register_ContactException("This ($name) is not a valid name.");
        }

    } //SetName

    public function GetType()
    {
        return $this->type;

    }

    public function SetType($type)
    {
        switch ($type) {
            case C3op_Register_ContactConstants::CONTACT_GENERAL:
            case C3op_Register_ContactConstants::CONTACT_CLIENT:
            case C3op_Register_ContactConstants::CONTACT_PARTNER:
            case C3op_Register_ContactConstants::CONTACT_ASSOCIATE:
            case C3op_Register_ContactConstants::CONTACT_CONTRACTOR:
            case C3op_Register_ContactConstants::CONTACT_SUPPLIER:
                $this->type = (int) $type;
                break;

            default:
                throw new C3op_Register_ContactException("Invalid type.");
                break;
        }
    }

    public function GetPhoneNumbers() {
        return $this->phoneNumbers;

    } //GetPhoneNumbers

    public function SetPhoneNumbers($phoneNumbers) {
        $newArray = array();
        if (is_array($phoneNumbers)) {
            $validator = new C3op_Register_ValidPhoneLocalNumber();

            foreach ($phoneNumbers as $k => $phoneNumber) {
                if ($phoneNumber instanceOf C3op_Register_ContactPhoneNumber) {
                    if ($validator->isValid($phoneNumber->GetLocalNumber())) {
                        $newArray[$k] = $phoneNumber;
                    } else {
                        throw new C3op_Projects_ActionException("A phone number must have at least a local number.");
                    }
                }
            }
        } else {
            throw new C3op_Projects_ActionException("Phone numbers must be organized in an array to be setted.");
        }
        $this->phoneNumbers = $newArray;
    } //SetPhoneNumbers

    public function AddPhoneNumber(C3op_Register_PhoneNumber $phoneNumber) {
        $validator = new C3op_Register_ValidPhoneLocalNumber();
        if ($validator->isValid($phoneNumber->GetLocalNumber())) {
            $this->phoneNumbers[] = $phoneNumber;
        } else {
            throw new C3op_Projects_ActionException("A phone number must have at least a local number.");
        }

    } //AddPhoneNumber

    public function RemovePhoneNumber($key) {

        if (is_array($this->phoneNumbers)) {
            if (!isset($this->phoneNumbers[$key])) {
                throw new C3op_Projects_ActionException("Phone number not found to be removed.");
            }
            unset($this->phoneNumbers[$key]);
        } else {
            throw new C3op_Projects_ActionException("There isn\'t phone numbers to remove");
        }

    } //SetPhoneNumbers


}