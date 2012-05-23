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
