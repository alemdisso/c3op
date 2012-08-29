<?php

class C3op_Register_LinkagePhoneNumber
    extends C3op_Register_PhoneNumber {

    protected $linkage;

    public function GetLinkage() {
        return $this->linkage;
    } //GetLinkage

    public function SetLinkage($linkage) {
        $validator = new C3op_Util_ValidPositiveInteger();
        if ($validator->isValid($linkage)) {
            if ($this->linkage != $linkage) {
                $this->linkage = $linkage;
            }
        } else {
            throw new C3op_Register_LinkageException("This ($linkage) is not a valid linkage.");
        }

    } //SetLinkage


}