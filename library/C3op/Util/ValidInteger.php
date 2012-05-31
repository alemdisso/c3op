<?php
class C3op_Util_ValidInteger extends Zend_Validate_Abstract {
    function isValid($value) {
        $nameValidator = new Zend_Validate_Int();
        return $nameValidator->isValid($value);
    }
}

?>
