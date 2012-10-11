<?php
class C3op_Projects_ProjectValidTitle extends Zend_Validate_Abstract {
    function isValid($value) {
        if ($value == "") {
            return false;
        }

        $titleValidator = new C3op_Util_ValidString;
        return $titleValidator->isValid($value);
    }
}

?>
