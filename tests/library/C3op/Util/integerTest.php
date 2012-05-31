<?php

class IntegerTest extends ControllerTestCase
{
    
    
    public function setUp() {
        parent::setUp();
    }
        
    public function testThatAPositiveIntegerValueIsValid() {
       $validator = new C3op_Util_ValidInteger();
       $aNumber = 89;
       $this->assertTrue($validator->isValid($aNumber));
    }
    
    public function testThatANegativeIntegerValueIsValid() {
       $validator = new C3op_Util_ValidInteger();
       $aNumber = -7;
       $this->assertTrue($validator->isValid($aNumber));
    }
    
    public function testThatAStringValueIsNotValid() {
       $validator = new C3op_Util_ValidInteger();
       $notANumber = "one two três quatorze";
       $this->assertFalse($validator->isValid($notANumber));
    }
    

}