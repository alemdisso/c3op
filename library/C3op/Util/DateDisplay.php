<?php
class C3op_Util_DateDisplay {
    
    public static function FormatDateToShow($rawMySqlDate)
    {
        $dateArray = explode("-", $rawMySqlDate);
        $formatedDate = $dateArray[2] . '/' . $dateArray[1] . '/' . $dateArray[0]; 
        return $formatedDate;
    }
}

