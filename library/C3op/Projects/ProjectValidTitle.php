<?php
class C3op_Projects_ProjectValidTitle extends Zend_Validate_Abstract {
    function isValid($value) {
        $titleValidator = new Zend_Validate_Regex("/^[0-9a-zA-ZÀ-ú]+[0-9A-Za-zÀ-ú\"\'\[\]\(\)\-\.\,\:\;\!\? ]{1,120}$/");
        
        return $titleValidator->isValid($value);
    }
}

?>
