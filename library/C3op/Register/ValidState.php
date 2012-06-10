<?php
class C3op_Register_ValidState extends Zend_Validate_Abstract {
    function isValid($value) {
        $nameValidator = new Zend_Validate_Regex(
            "/^AC|AL|AP|AM|BA|CE|DF|ES|GO|MA|MT|MS|MG|PA|PB|PE|PI|PR|RJ|RN|RS|RO|RR|SC|SP|SE|TO$/"
        );
        return $nameValidator->isValid($value);
    }
}

?>
