<?php

class C3op_Register_ContactEmail
    extends C3op_Register_Email {

    protected $contact;

    public function GetContact() {
        return $this->contact;
    } //GetContact

    public function SetContact($contact) {
        $validator = new C3op_Util_ValidPositiveInteger();
        if ($validator->isValid($contact)) {
            if ($this->contact != $contact) {
                $this->contact = $contact;
            }
        } else {
            throw new C3op_Register_ContactException("This ($contact) is not a valid contact.");
        }

    } //SetContact


}