<?php

class PhoneValidationTest extends ControllerTestCase
{


    public function setUp() {
        parent::setUp();
    }

    public function testThatCanValidateValidPhoneLocalNumber() {
       $validator = new C3op_Register_ValidPhoneLocalNumber();
       $validPhoneNumber = "2345 6789";
       $this->assertTrue($validator->isValid($validPhoneNumber));
       $validPhoneNumber = "2345-6789";
       $this->assertTrue($validator->isValid($validPhoneNumber));
       $validPhoneNumber = "2345.6789";
       $this->assertTrue($validator->isValid($validPhoneNumber));
       $validPhoneNumber = "2345-26789";
       $this->assertTrue($validator->isValid($validPhoneNumber));
       $validPhoneNumber = "2345 63789";
       $this->assertTrue($validator->isValid($validPhoneNumber));
       $validPhoneNumber = "2345.76789";
       $this->assertTrue($validator->isValid($validPhoneNumber));
       $validPhoneNumber = "23456789";
       $this->assertTrue($validator->isValid($validPhoneNumber));
    }

    public function testThatCanValidateValidPhoneAreaCode() {
       $validator = new C3op_Register_ValidPhoneAreaCode();
       $validAreaCode = "23";
       $this->assertTrue($validator->isValid($validAreaCode));
       $validAreaCode = "55 21";
       $this->assertTrue($validator->isValid($validAreaCode));
    }




}