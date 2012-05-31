<?php
class C3op_Util_ValidPositiveInteger extends Zend_Validate_Abstract {
    function isValid($value) {
        $nameValidator = new C3op_Util_ValidInteger();
        return ($nameValidator->isValid($value)) && ($value >= 0);
    }
}

?>
