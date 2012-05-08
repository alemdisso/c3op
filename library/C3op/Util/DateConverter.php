<?php

class C3op_Util_DateConverter
{
    public function convertDateToMySQLFormat($date)
    {
            class_exists('C3op_Util_ValidDate') || require "validDate.php";
            $dateValidator = new C3op_Util_ValidDate();
            if($dateValidator->isValid($date)) {
                    return implode("-", array_reverse(explode(!strstr($date, '/') ? "-" : "/", $date)));
            } else {
               throw new C3op_Util_DateException('Invalid date!');
            }
    }
}

?>
