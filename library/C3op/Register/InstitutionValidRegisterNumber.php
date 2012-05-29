<?php
class C3op_Register_InstitutionValidRegisterNumber extends Zend_Validate_Abstract {
    function isValid($value) {
        $registernumberValidator = new Zend_Validate_Regex("/^[0-9a-zA-ZÀ-ú]+[0-9A-Za-zÀ-ú\'\[\]\(\)\-\.\,\:\;\!\?\/ ]{1,120}$/");
        return $registernumberValidator->isValid($value);
    }
}

?>
