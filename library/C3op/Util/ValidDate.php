<?php

class C3op_Util_ValidDate extends Zend_Validate_Abstract {
    
    public function isValid($value) {
            $date = preg_split("/[- :|\\/]/", $value);
            if (count($date) < 3)  {
                    return false;                
            } else if(!checkdate($date[1], $date[0], $date[2]) and !checkdate($date[1], $date[2], $date[0])) {
                    return false;
            }
            return true;        
    }
}

?>
