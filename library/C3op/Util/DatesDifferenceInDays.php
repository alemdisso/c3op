<?php

class C3op_Util_DatesDifferenceInDays {

    public function differenceInDays($firstDate, $secondDate)
    {
        $datediff = $firstDate - $secondDate;
        $differenceInDays = floor($datediff/(60*60*24));

        return ($differenceInDays);
    }

}

?>
