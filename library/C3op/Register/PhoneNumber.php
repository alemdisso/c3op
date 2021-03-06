<?php

class C3op_Register_PhoneNumber {

    protected $id;
    protected $areaCode;
    protected $localNumber;
    protected $label;

    function __construct($id=0, $areaCode="", $localNumber="", $label="") {
        $this->id = (int)$id;
        $this->areaCode = $areaCode;
        $this->localNumber = $localNumber;
        $this->label = $label;
    }

    public function GetId() {
        return $this->id;
    } //GetId

    public function SetId($id) {
        if (($this->id == 0) && ($id > 0)) {
            $this->id = (int)$id;
        } else {
            throw new C3op_Register_PhoneNumberException('It\'s not possible to change a phone number\'s ID');
        }
    } //SetId

    public function GetAreaCode() {
        return $this->areaCode;
    } //GetAreaCode

    public function SetAreaCode($areaCode) {
        $validator = new C3op_Register_ValidPhoneAreaCode();
        if (($areaCode == "") || ($validator->isValid($areaCode))) {
            if ($this->areaCode != $areaCode) {
                $this->areaCode = $areaCode;
            }
        } else {
            throw new C3op_Register_ContactException("This ($areaCode) is not a valid area code.");
        }
    } //SetAreaCode

    public function GetLocalNumber() {
        return $this->localNumber;
    } //GetLocalNumber

    public function SetLocalNumber($localNumber) {
        $validator = new C3op_Register_ValidPhoneLocalNumber();
        if ($validator->isValid($localNumber)) {
            if ($this->localNumber != $localNumber) {
                $this->localNumber = $localNumber;
            }
        } else {
            throw new C3op_Register_ContactException("This ($localNumber) is not a valid label.");
        }
    } //SetLocalNumber

    public function GetLabel() {
        return $this->label;
    } //GetLabel

    public function SetLabel($label) {
        $validator = new C3op_Util_ValidString();
        if (($label == "") || ($validator->isValid($label))) {
            if ($this->label != $label) {
                $this->label = $label;
            }
        } else {
            throw new C3op_Register_ContactException("This ($label) is not a valid label.");
        }
    } //SetLabel
}