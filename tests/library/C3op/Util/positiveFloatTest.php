<?php

class PositiveFloatTest extends ControllerTestCase
{
    private $validator;
    
    public function setUp()
    {
        parent::setUp();
        $this->validator = new C3op_Util_ValidPositiveFloat();
    }
        
    public function testThatAPositiveFloatValueIsValid()
    {
      $aFloat = 1.76;
       $this->assertTrue($this->validator->isValid($aFloat));
    }
    
    public function testThatAPositiveIntegerValueIsValid()
    {
       $aFloat = 89;
       $this->assertTrue($this->validator->isValid($aFloat));
    }
    
    public function testThatANegativeFloatValueIsNotValid()
    {
       $aFloat = -3.0008;
       $this->assertFalse($this->validator->isValid($aFloat));
    }
    
    public function testThatAStringValueIsNotValid()
    {
       $notAFloat = "minus one float point thirty-three";
       $this->assertFalse($this->validator->isValid($notAFloat));
    }
}