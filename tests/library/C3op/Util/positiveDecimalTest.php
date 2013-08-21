<?php

class PositiveDecimalTest extends ControllerTestCase
{
    private $validator;

    public function setUp()
    {
        parent::setUp();
        $this->validator = new C3op_Util_ValidPositiveDecimal();
    }

    public function testThatAPositiveDecimalValueIsValid()
    {
      $aDecimal = 1.76;
       $this->assertTrue($this->validator->isValid($aDecimal));
    }

    public function testThatAPositiveIntegerValueIsValid()
    {
       $aDecimal = 89;
       $this->assertTrue($this->validator->isValid($aDecimal));
    }

    public function testThatANegativeDecimalValueIsNotValid()
    {
       $aDecimal = -3.0008;
       $this->assertFalse($this->validator->isValid($aDecimal));
    }

    public function testThatAStringValueIsNotValid()
    {
       $notADecimal = "minus one decimal point thirty-three";
       $this->assertFalse($this->validator->isValid($notADecimal));
    }
}