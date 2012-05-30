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
 
    public function testIfCanAttributeShortNameToInstitution()
    {            
        $value = "some shorter name";
        $this->institution->SetShortName($value);
        $this->assertEquals($this->institution->getShortName(), $value);
    }
    
    public function testIfCanSetIfAnInstitutionIsALegalEntityOrNot()
    {            
        $value = "1";
        $this->institution->SetLegalEntity($value);
        $this->assertEquals($this->institution->getLegalEntity(), $value);
        $value = "0";
        $this->institution->SetLegalEntity($value);
        $this->assertEquals($this->institution->getLegalEntity(), $value);
    }
    
    public function testIfCanSetRegisterNumberToAnInstitution()
    {            
        $value = "9876543210/0001-32";
        $this->institution->SetRegisterNumber($value);
        $this->assertEquals($this->institution->GetRegisterNumber(), $value);
    }
    
    public function testValidatorOfRegisterNumber()
    {
        $validator = new C3op_Register_InstitutionValidRegisterNumber;
        $value = "60.746.948/0001-12";
        $this->assertTrue($validator->isValid($value));
    }
    
    public function testIfCanSetThatAnInstitutionHasAStateRegistrationOrNOt()
    {            
        $value = "1";
        $this->institution->SetStateRegistration($value);
        $this->assertEquals($this->institution->getStateRegistration(), $value);
        $value = "0";
        $this->institution->SetStateRegistration($value);
        $this->assertEquals($this->institution->getStateRegistration(), $value);
    }
    
    public function testIfCanSetLocalRegisterNumberToAnInstitution()
    {            
        $value = "13.304.211-1";
        $this->institution->SetLocalRegisterNumber($value);
        $this->assertEquals($this->institution->GetLocalRegisterNumber(), $value);
    }
    
    public function testIfCanSetStreetToAnInstitution()
    {            
        $value = "Rua Nova Ordem";
        $this->institution->SetStreet($value);
        $this->assertEquals($this->institution->GetStreet(), $value);
    }
    
    public function testIfCanSetStreetNumberToAnInstitution()
    {            
        $value = "51";
        $this->institution->SetStreetNumber($value);
        $this->assertEquals($this->institution->GetStreetNumber(), $value);
    }
    
    public function testIfCanSetAddressComplementToAnInstitution()
    {            
        $value = "Bloco A - Coluna F - Grupo 450";
        $this->institution->SetAddressComplement($value);
        $this->assertEquals($this->institution->GetAddressComplement(), $value);
    }
    
    public function testIfCanSetDistrictToAnInstitution()
    {            
        $value = "São Gonçalo";
        $this->institution->SetDistrict($value);
        $this->assertEquals($this->institution->GetDistrict(), $value);
    }
    
    public function testIfCanSetCityToAnInstitution()
    {            
        $value = "Porciúncula";
        $this->institution->SetCity($value);
        $this->assertEquals($this->institution->GetCity(), $value);
    }
    
    public function testIfCanSetWebsiteToAnInstitution()
    {            
        $value = "www.umsite.com";
        $this->institution->SetWebsite($value);
        $this->assertEquals($this->institution->GetWebsite(), $value);
    }
    
}
