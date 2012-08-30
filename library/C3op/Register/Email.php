<?php

class C3op_Register_Email {

    protected $id;
    protected $email;
    protected $label;

    function __construct($id=0, $email="", $label="") {
        $this->id = (int)$id;
        $this->email = $email;
        $this->label = $label;
    }

    public function GetId() {
        return $this->id;
    } //GetId

    public function SetId($id) {
        if (($this->id == 0) && ($id > 0)) {
            $this->id = (int)$id;
        } else {
            throw new C3op_Register_EmailException('It\'s not possible to change a contact\'s ID');
        }
    } //SetId

    public function GetEmail() {
        return $this->email;
    } //GetEmail

    public function SetEmail($email) {
        $validator = new C3op_Register_ValidPhoneEmail();
        if ($validator->isValid($email)) {
            if ($this->email != $email) {
                $this->email = $email;
            }
        } else {
            throw new C3op_Register_ContactException("This ($email) is not a valid one.");
        }
    } //SetEmail

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