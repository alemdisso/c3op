<?php

class C3op_Util_DateCompare {

    public function isPast($time)
    {
        return (strtotime($time) < time());
    }

    public function isFuture($time)
    {
        return (strtotime($time) > time());
    }

    public function isToday($time) // midnight second
    {
        return (strtotime($time) === strtotime('today'));
    }

    public function isBefore($thisTime, $thatTime)
    {
        return (strtotime($thisTime) < strtotime($thatTime));

    }

    public function isAfter($thisTime, $thatTime)
    {
        return (strtotime($thisTime) > strtotime($thatTime));

    }

    public function isAtSameTime($thisTime, $thatTime)
    {
        return (strtotime($thisTime) === strtotime($thatTime));

    }

}

?>
