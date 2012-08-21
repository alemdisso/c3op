<?php
class C3op_Register_ValidPhoneLocalNumber extends Zend_Validate_Abstract {
    function isValid($value) {
        $nameValidator = new Zend_Validate_Regex("/^[0-9]{3,4}(-| |\.)?[0-9]{4,5}$/");
        return $nameValidator->isValid($value);
    }
}

?>
