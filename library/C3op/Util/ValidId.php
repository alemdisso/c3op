<?php

class C3op_Util_ValidId extends Zend_Validate_Abstract {
    
    public function isValid($value) {
        $intValidator = new Zend_Validate_Int(array('locale' => 'br'));
        $positiveValidator = new Zend_Validate_GreaterThan(0);
        if (($intValidator->isValid($value)) && ($positiveValidator->isValid($value))) {
            return true;
        } else {
            return false;
        }
    }
}

?>