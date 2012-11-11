<?php
class C3op_Register_ContactValidName extends Zend_Validate_Abstract {
    function isValid($value) {
        if ($value == "") {
            return false;
        }

        $titleValidator = new C3op_Util_ValidString;
        return $titleValidator->isValid($value);
    }
}

?>
