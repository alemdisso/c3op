<?php
class C3op_Register_ValidZipCode extends Zend_Validate_Abstract {
    function isValid($value) {
        $nameValidator = new Zend_Validate_Regex("/^[0-9]{2}.?[0-9]{3}-?[0-9]{3}$/");
        return $nameValidator->isValid($value);
    }
}

?>
