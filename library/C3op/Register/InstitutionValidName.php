<?php
class C3op_Register_InstitutionValidName extends Zend_Validate_Abstract {
    function isValid($value) {
        $nameValidator = new Zend_Validate_Regex("/^[0-9a-zA-ZÀ-ú]+[0-9A-Za-zÀ-ú\'\[\]\(\)\-\.\,\:\;\!\?\/ ]{1,120}$/");
        return $nameValidator->isValid($value);
    }
}

?>
