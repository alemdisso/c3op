<?php
class C3op_Util_ValidEmail extends Zend_Validate_Abstract {
    function isValid($value) {
        $nameValidator = new Zend_Validate_EmailAddress();
        return $nameValidator->isValid($value);
    }
}

?>
