<?php
class C3op_Util_ValidPositiveDecimal extends Zend_Validate_Abstract {
    function isValid($value) {
        $nameValidator = new C3op_Util_ValidDecimal();
        return ($nameValidator->isValid($value)) && ($value >= 0.0);
    }
}

?>
