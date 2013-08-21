<?php

class C3op_Util_DatesDifferenceInDays {

    public function differenceInDays($firstDate, $secondDate)
    {
        $datediff = $firstDate - $secondDate;
        $differenceInDays = $datediff/(60*60*24);
        $differenceInDays= ($differenceInDays>0)  ? floor($differenceInDays) : ceil ($differenceInDays);
        return ($differenceInDays);
    }
}

?>
