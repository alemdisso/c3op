<?php

class DatesDifferenceInDaysTest extends ControllerTestCase
{
    public function setUp() {
        parent::setUp();
    }

    public function testThatComparingTodayWithNowGivesZeroDayDiff()
    {
        $today = date("j-n-Y");
        $now = time();

        $diff = new C3op_Util_DatesDifferenceInDays();

        $this->assertEquals($diff->differenceInDays($now, strtotime($today)), 0);
        $this->assertEquals($diff->differenceInDays(strtotime($today), $now), 0);
    }

    public function testThatComparingTomorrowWithNowGivesOnePositiveDayDiff()
    {
        $today = date("j-n-Y");

        //$tomorrow = strtotime($today . ' +1 day');
        $now = time();
        $tomorrow = date('Y-m-d 23:59:59', strtotime('+1 day', strtotime($today)));
        //$tomorrow = date("Y-m-d", mktime("23","59","59", date("n", $now),date("j",$now)+ 1 ,date("Y", $now)));
        $diff = new C3op_Util_DatesDifferenceInDays();
        //$this->assertEquals($tomorrow, $now);

        $this->assertEquals($diff->differenceInDays(strtotime($tomorrow), $now), 1);
        $this->assertEquals($diff->differenceInDays($now, strtotime($tomorrow)), -1);
    }

}