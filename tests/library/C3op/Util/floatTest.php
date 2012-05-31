<?php

class FloatTest extends ControllerTestCase
{
    
    
    public function setUp() {
        parent::setUp();
    }
        
    public function testThatAPositiveFloatValueIsValid() {
       $validator = new C3op_Util_ValidFloat();
       $aFloat = 1.76;
       $this->assertTrue($validator->isValid($aFloat));
    }
    
    public function testThatAPositiveIntegerValueIsValid() {
       $validator = new C3op_Util_ValidFloat();
       $aFloat = 89;
       $this->assertTrue($validator->isValid($aFloat));
    }
    
    public function testThatANegativeFloatValueIsValid() {
       $validator = new C3op_Util_ValidFloat();
       $aFloat = -3.0008;
       $this->assertTrue($validator->isValid($aFloat));
    }
    
    public function testThatAStringValueIsNotValid() {
       $validator = new C3op_Util_ValidFloat();
       $notAFloat = "one float point thirty-three";
       $this->assertFalse($validator->isValid($notAFloat));
    }
    

}