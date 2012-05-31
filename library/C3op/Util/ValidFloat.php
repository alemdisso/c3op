<?php
class C3op_Util_ValidFloat extends Zend_Validate_Abstract {
    function isValid($value) {
        $nameValidator = new Zend_Validate_Regex("/^-?[0-9]*\.?[0-9]*$/");
        return $nameValidator->isValid($value);
    }
}

?>
