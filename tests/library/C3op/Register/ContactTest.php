<?php

class ContactTest extends ControllerTestCase
{
    private $contact;
    public function setUp() {
        $this->contact = new C3op_Register_Contact();
        parent::setUp();
    }

    public function testIfNewContactIsEmpty()
    {
        $this->assertEquals($this->contact->getId(), 0);
        $this->assertEquals($this->contact->getName(), "");
        $this->assertEquals($this->contact->getType(), "");

    }

    public function testIfCanGetEmptyArrayForNewContactPhoneNumbers()
    {
        $phonesArray = $this->contact->getPhoneNumbers();
        $this->assertTrue(is_array($phonesArray));
        $this->assertEquals(0, count($phonesArray));
    }

    public function testIfCanSetAPhoneNumberToContact()
    {
        $aPhoneNumber = new C3op_Register_PhoneNumber(0, "21", "2234-5678");

        $this->contact->AddPhoneNumber($aPhoneNumber);
        $phonesArray = $this->contact->getPhoneNumbers();
        $this->assertTrue(is_array($phonesArray));
        $this->assertEquals(1, count($phonesArray));

    }

    public function testIfCanSetTwoDifferentPhoneNumbersToContact()
    {
        $aPhoneNumber = new C3op_Register_PhoneNumber(0, "21", "2234-5678");

        $this->contact->AddPhoneNumber($aPhoneNumber);
        $otherPhoneNumber = new C3op_Register_PhoneNumber(0, "21", "8989-0123");
        $someElsePhoneNumber = new C3op_Register_PhoneNumber(0, "21", "3267-7843");

        $this->contact->AddPhoneNumber($otherPhoneNumber);
        $phonesArray = $this->contact->getPhoneNumbers();
        $this->assertTrue(is_array($phonesArray));
        $this->assertEquals(2, count($phonesArray));
        $this->assertTrue(in_array($aPhoneNumber, $phonesArray));
        $this->assertTrue(in_array($otherPhoneNumber, $phonesArray));
        $this->assertTrue(!in_array($someElsePhoneNumber, $phonesArray));
    }

    public function testIfCanChangeAPhoneNumberRelatedToContact()
    {
        $aId = 0;
        $aPhoneNumber = new C3op_Register_ContactPhoneNumber($aId, "21", "2234-5678");
        $this->contact->AddPhoneNumber($aPhoneNumber);

        $otherId = 1;
        $otherPhoneNumber = new C3op_Register_ContactPhoneNumber($otherId, "21", "8989-0123");
        $this->contact->AddPhoneNumber($otherPhoneNumber);

        $newLocalNumber = "5678-0000";
        $phonesArray = $this->contact->getPhoneNumbers();
        $aPhoneNumber->SetLocalNumber($newLocalNumber);
        $this->contact->SetPhoneNumbers($phonesArray);
        $phonesArray = $this->contact->getPhoneNumbers();

        $this->assertTrue(is_array($phonesArray));
        $this->assertEquals(2, count($phonesArray));
        $this->assertEquals($newLocalNumber, $phonesArray[$aId]->GetLocalNumber());
        $this->assertTrue(in_array($otherPhoneNumber, $phonesArray));
    }


    public function testIfCanRemovePhoneNumbersAssociatedToContact()
    {
        $phoneNumber1 = new C3op_Register_PhoneNumber(0, "21", "2234-5678");

        $k1 = $this->contact->AddPhoneNumber($phoneNumber1);

        $phoneNumber2 = new C3op_Register_PhoneNumber(0, "21", "8989-0123");
        $k2 = $this->contact->AddPhoneNumber($phoneNumber2);

        $phonesArray = $this->contact->getPhoneNumbers();
        $this->assertEquals(2, count($phonesArray));
        $this->assertTrue(in_array($phoneNumber1, $phonesArray));
        $this->assertTrue(in_array($phoneNumber2, $phonesArray));
        $this->contact->RemovePhoneNumber($phoneNumber1);
        $phonesArray = $this->contact->GetPhoneNumbers();
        $this->assertTrue(!in_array($phoneNumber1, $phonesArray));
        $this->assertEquals(1, count($phonesArray));
        $this->contact->RemovePhoneNumber($phoneNumber2);
        $phonesArray = $this->contact->GetPhoneNumbers();
        $this->assertTrue(!in_array($phoneNumber2, $phonesArray));
        $this->assertEquals(0, count($phonesArray));
    }


    public function testIfCanAttributeNameToContact()
    {
        $someName = "some name with ç á and ã";
        $this->contact->SetName($someName);
        $this->assertEquals($this->contact->getName(), $someName);
    }

    public function testIfCanChangeContactName()
    {
        $this->contact->SetName("some name");
        $this->assertEquals($this->contact->getName(), "some name");
        $this->contact->SetName("other name");
        $this->assertEquals($this->contact->getName(), "other name");
    }

/*
 * @expectedException C3op_Register_ContactException
 */

    public function testIfSettingEmptyContactNameRaisesError()
    {
        $this->setExpectedException('C3op_Register_ContactException');
        $this->contact->SetName("");
    }

/*
 * @expectedException C3op_Register_ContactException
 * @depends testIfCanAttributeNameToContact()
 */

    public function testThatSettingEmptyNameDoesntChangename()
    {
        $originalName = $this->contact->getName();
        $this->setExpectedException('C3op_Register_ContactException');
        $this->contact->SetName("");
        $this->assertEquals($this->contact->getName(), $originalName);
    }

    public function testCanSetType()
    {
        $someType = C3op_Register_ContactConstants::CONTACT_CONTRACTOR;
        $this->contact->SetType($someType);
        $this->assertEquals($this->contact->getType(), $someType);
    }

    public function testTypeCantBeAlpha()
    {
        $someStrangeType = "a string";
        $this->setExpectedException('C3op_Register_ContactException');
        $this->contact->SetType($someStrangeType);
        $textType = "-1";
        $this->setExpectedException('C3op_Register_ContactException');
        $this->contact->SetType($textType);
    }

    public function testCanChangeTypeOfContact()
    {
        $firstType = C3op_Register_ContactConstants::CONTACT_CLIENT;
        $newType = C3op_Register_ContactConstants::CONTACT_SUPPLIER;
        $this->contact->SetType($firstType);
        $this->assertEquals($this->contact->getType(), (float)$firstType);
        $this->contact->SetType($newType);
        $this->assertEquals($this->contact->getType(), (float)$newType);
    }

}
