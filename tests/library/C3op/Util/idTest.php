<?php

class IdTest extends ControllerTestCase
{
    
    
    public function setUp() {
        parent::setUp();
    }
        
    public function testThat1IsValidId() {
       $validator = new C3op_Util_ValidId();
       $id=1;
       $this->assertTrue($validator->isValid($id));
    }
    
    public function testThatBigIdWithCommaAsDecimalSeparator1IsValidId() {
       $validator = new C3op_Util_ValidId();
       $id='1.234';
       $this->assertTrue($validator->isValid($id));
    }
    
    public function testThatNegativeIsntValidId() {
       $validator = new C3op_Util_ValidId();
       $id=-12;
       $this->assertFalse($validator->isValid($id));
    }
    
}