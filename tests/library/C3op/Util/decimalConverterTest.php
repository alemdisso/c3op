<?php

class DecimalConverterTest extends ControllerTestCase
{
    public function setUp() {
        parent::setUp();
    }

    public function testThatCanIdentifyDecimalComma() {
       $converter = new C3op_Util_DecimalConverter();
       $value = "20.000,00";
       $this->assertTrue($converter->identifyDecimalComma($value));
       $value = "20000,00";
       $this->assertTrue($converter->identifyDecimalComma($value));
       $value = "20000.00";
       $this->assertFalse($converter->identifyDecimalComma($value));
    }

    public function testThatCanConvertDecimalCommaToDecimalDot() {
       $converter = new C3op_Util_DecimalConverter();
       $value = "20.000,00";
       $this->assertEquals($converter->convertDecimalCommaToDecimalDot($value), "20000.00");
    }


}