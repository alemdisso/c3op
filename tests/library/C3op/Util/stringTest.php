<?php

class StringTest extends ControllerTestCase
{
    
    
    public function setUp() {
        parent::setUp();
    }
        
    public function testThatAnEmptyStringIsAValidString() {
       $validator = new C3op_Util_ValidString();
       $emptyString = "";
       $this->assertTrue($validator->isValid($emptyString));
    }
    
    public function testThatATypicalStringIsAValidString() {
       $validator = new C3op_Util_ValidString();
       $string = "ABCDE fghij";
       $this->assertTrue($validator->isValid($string));
    }
    
    public function testThatEvenSomeTinyStringsAreValidStrings() {
       $validator = new C3op_Util_ValidString();
       $string = "A";
       $this->assertTrue($validator->isValid($string));
       $string = "bc";
       $this->assertTrue($validator->isValid($string));
       $string = "0";
       $this->assertTrue($validator->isValid($string));
    }
    

}