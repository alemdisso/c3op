<?php
class C3op_Register_ValidPhoneAreaCode extends Zend_Validate_Abstract {
    function isValid($value) {
        $nameValidator = new Zend_Validate_Regex("/^([0-9]{1,3})?( |-|\.)?[0-9]{2}$/");
        return $nameValidator->isValid($value);
    }
}

?>

