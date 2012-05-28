<?php

class InstitutionTest extends ControllerTestCase
{
    private $institution;
    public function setUp() {
        $this->institution = new C3op_Register_Institution();
        parent::setUp();
    }
    
    public function testIfNewInstitutionIsEmpty()
    {            
        $this->assertEquals($this->institution->getId(), 0);
        $this->assertEquals($this->institution->getName(), "");
        $this->assertEquals($this->institution->getType(), "");
        
    }
 
    public function testIfCanAttributeNameToInstitution()
    {            
        $someName = "some name with ç á and ã";
        $this->institution->SetName($someName);
        $this->assertEquals($this->institution->getName(), $someName);
    }
 
    public function testIfCanChangeInstitutionName()
    {            
        $this->institution->SetName("some name");
        $this->assertEquals($this->institution->getName(), "some name");
        $this->institution->SetName("other name");
        $this->assertEquals($this->institution->getName(), "other name");
    }
 
/*
 * @expectedException C3op_Register_InstitutionException
 */
    
    public function testIfSettingEmptyInstitutionNameRaisesError()
    {            
        $this->setExpectedException('C3op_Register_InstitutionException');
        $this->institution->SetName("");  
    }
 
/*
 * @expectedException C3op_Register_InstitutionException
 * @depends testIfCanAttributeNameToInstitution()
 */
    
    public function testThatSettingEmptyNameDoesntChangename()
    {   
        $originalName = $this->institution->getName();
        $this->setExpectedException('C3op_Register_InstitutionException');
        $this->institution->SetName("");   
        $this->assertEquals($this->institution->getName(), $originalName);
    }
 
    public function testCanSetType()
    {   
        $someType = C3op_Register_InstitutionConstants::INSTITUTION_PRIVATE;
        $this->institution->SetType($someType);
        $this->assertEquals($this->institution->getType(), $someType);
    }
 
    public function testTypeCantBeAlpha()
    {           
        $someStrangeType = "a string";
        $this->setExpectedException('C3op_Register_InstitutionException');
        $this->institution->SetType($someStrangeType);
        $textType = "-1";
        $this->setExpectedException('C3op_Register_InstitutionException');
        $this->institution->SetType($textType);
    }
    
    public function testCanChangeTypeOfInstitution()
    {        
        $firstType = C3op_Register_InstitutionConstants::INSTITUTION_NGO;
        $newType = C3op_Register_InstitutionConstants::INSTITUTION_RESEARCH;
        $this->institution->SetType($firstType);
        $this->assertEquals($this->institution->getType(), (float)$firstType);
        $this->institution->SetType($newType);
        $this->assertEquals($this->institution->getType(), (float)$newType);
    }
 
}
