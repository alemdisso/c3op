<?php

class C3op_Register_Email {

    protected $id;
    protected $address;
    protected $label;

    function __construct($id=0, $address="", $label="") {
        $this->id = (int)$id;
        $this->address = $address;
        $this->label = $label;
    }

    public function GetId() {
        return $this->id;
    } //GetId

    public function SetId($id) {
        if (($this->id == 0) && ($id > 0)) {
            $this->id = (int)$id;
        } else {
            throw new C3op_Register_EmailException('It\'s not possible to change a messenger\'s ID');
        }
    } //SetId

    public function GetAddress() {
        return $this->address;
    } //GetAddress

    public function SetAddress($address) {
        $validator = new C3op_Util_ValidEmail();
        if ($validator->isValid($address)) {
            if ($this->address != $address) {
                $this->address = $address;
            }
        } else {
            throw new C3op_Register_ContactException("This ($address) is not a valid one.");
        }
    } //SetAddress

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