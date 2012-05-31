<?php
class C3op_Util_ValidPositiveFloat extends Zend_Validate_Abstract {
    function isValid($value) {
        $nameValidator = new C3op_Util_ValidFloat();
        return ($nameValidator->isValid($value)) && ($value >= 0.0);
    }
}

?>
