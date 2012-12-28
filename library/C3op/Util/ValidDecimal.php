<?php
class C3op_Util_ValidDecimal extends Zend_Validate_Abstract {
    function isValid($value) {
        $nameValidator = new Zend_Validate_Regex("/^[+-]?[0-9]{1,3}(?:(?:\,[0-9]{3})*(?:.[0-9]{2})?|(?:\.[0-9]{3})*(?:\,[0-9]{2})?|[0-9]*(?:[\.\,][0-9]{2})?)*$/");
        return $nameValidator->isValid($value);
    }
}

?>
