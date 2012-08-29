<?php

class PhoneNumberTest extends ControllerTestCase
{
    private $phoneNumber;
    public function setUp() {
        $this->phoneNumber = new C3op_Register_PhoneNumber();
        parent::setUp();
    }

    public function testCanCreateAPhoneNumber()
    {

        $this->assertTrue($this->phoneNumber instanceOf C3op_Register_PhoneNumber);
    }

    public function testCanCreateAContactPhoneNumber()
    {
        $contactPhoneNumber = new C3op_Register_ContactPhoneNumber();

        $this->assertTrue($contactPhoneNumber instanceOf C3op_Register_ContactPhoneNumber);
    }

    public function testCanSetAContactToAContactPhoneNumber()
    {
        $contactPhoneNumber = new C3op_Register_ContactPhoneNumber();

        $contactId = 6;
        $contactPhoneNumber->SetContact($contactId);

        $this->assertEquals($contactId, $contactPhoneNumber->GetContact());
    }

    public function testCanSetAnAreaCodeToAContactPhoneNumber()
    {
        $contactPhoneNumber = new C3op_Register_ContactPhoneNumber();

        $areaCode = "021";
        $contactPhoneNumber->SetAreaCode($areaCode);

        $this->assertEquals($areaCode, $contactPhoneNumber->GetAreaCode());
    }

}
