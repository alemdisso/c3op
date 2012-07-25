<?php

class EmailTest extends ControllerTestCase
{
    
    
    public function setUp() {
        parent::setUp();
    }
        
    public function testThatCanValidateValidEmail() {
       $validator = new C3op_Util_ValidEmail();
       $validEmail = "rodm67@globo.com";
       $this->assertTrue($validator->isValid($validEmail));
    }
    
    public function testThatCanBadEmailIsntValid() {
       $validator = new C3op_Util_ValidEmail();
       $validEmail = "rodm is a good fellow";
       $this->assertFalse($validator->isValid($validEmail));
    }
    

}