<?php

class LongStringTest extends ControllerTestCase
{


    public function setUp() {
        parent::setUp();
    }

    public function testThatAnEmptyStringIsAValidString() {
       $validator = new C3op_Util_ValidLongString();
       $emptyString = "";
       $this->assertTrue($validator->isValid($emptyString));
    }

    public function testThatATypicalStringIsAValidString() {
       $validator = new C3op_Util_ValidLongString();
       $string = "ABCDE fghij";
       $this->assertTrue($validator->isValid($string));
    }

    public function testThatEvenSomeTinyStringsAreValidStrings() {
       $validator = new C3op_Util_ValidLongString();
       $string = "A";
       $this->assertTrue($validator->isValid($string));
       $string = "bc";
       $this->assertTrue($validator->isValid($string));
       $string = "0";
       $this->assertTrue($validator->isValid($string));
    }

    public function testThatCanUsePercentAtString() {
       $validator = new C3op_Util_ValidLongString();
       $string = "ABCDE 50%";
       $this->assertTrue($validator->isValid($string));
    }

    public function testThatAReallyLongStringIsValid() {
       $validator = new C3op_Util_ValidLongString();
       $chunk = "abcde fghijkl mno pq rstuvwx yz. 1234 567 890. xxx";
       $string = "";
       for ($i=0; $i < 10000; $i+=strlen($chunk)) {
           $string .= $chunk;
       }
       $this->assertTrue($validator->isValid($string));
    }

    public function testThatCanUseSomeOtherCharsAtString() {
       $validator = new C3op_Util_ValidLongString();
       $string = "ABCDE 50% ª º & sons. ";
       $this->assertTrue($validator->isValid($string));
    }


}