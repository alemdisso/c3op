<?php

class ProjectTest extends ControllerTestCase
{
    private $project;
    public function setUp() {
        $this->project = new C3op_Projects_Project();
        parent::setUp();
    }
    
    public function testIfNewProjectIsEmpty()
    {            
        $this->assertEquals($this->project->getId(), 0);
        $this->assertEquals($this->project->getTitle(), "");
        $this->assertEquals($this->project->getValue(), 0);
        
    }
 
    public function testIfCanAttributeTitleToProject()
    {            
        $someTitle = "some title with ç á and ã";
        $this->project->SetTitle($someTitle);
        $this->assertEquals($this->project->getTitle(), $someTitle);
    }
 
    public function testIfCanChangeProjectTitle()
    {            
        $this->project->SetTitle("some title");
        $this->assertEquals($this->project->getTitle(), "some title");
        $this->project->SetTitle("other title");
        $this->assertEquals($this->project->getTitle(), "other title");
    }
 
/*
 * @expectedException C3op_Projects_ProjectException
 */
    
    public function testIfSettingEmptyProjectTitleRaisesError()
    {            
        $this->setExpectedException('C3op_Projects_ProjectException');
        $this->project->SetTitle("");  
    }
 
/*
 * @expectedException C3op_Projects_ProjectException
 * @depends testIfCanAttributeTitleToProject()
 */
    
    public function testThatSettingEmptyTitleDoesntChangetitle()
    {   
        $originalTitle = $this->project->getTitle();
        $this->setExpectedException('C3op_Projects_ProjectException');
        $this->project->SetTitle("");   
        $this->assertEquals($this->project->getTitle(), $originalTitle);
    }
 
    public function testCanSetDateOfBegin()
    {   
        $someDate = "20-12-2012";
        $this->project->SetDateBegin($someDate);
        $this->assertEquals($this->project->getDateBegin(), $someDate);
    }
 
    public function testCanChangeDateOfBegin()
    {   
        $firstDate = "01-12-2012";
        $newDate = "10-07-2012";
        $this->project->SetDateBegin($firstDate);
        $this->project->SetDateBegin($newDate);
        $this->assertEquals($this->project->getDateBegin(), $newDate);
    }
 
    public function testIfSetingBadDateOfBeginRaiseAnError()
    {   
        $crazyDate = "notadateatall";
        $this->setExpectedException('C3op_Projects_ProjectException');
        $this->project->SetDateBegin($crazyDate);
    }
 
    public function testCanSetValue()
    {   
        $someValue = 230743.02;
        $this->project->SetValue($someValue);
        $this->assertEquals($this->project->getValue(), $someValue);
    }
 
    public function testValueMustBePositiveNumber()
    {           
        $someStrangeValue = "-564000";
        $this->setExpectedException('C3op_Projects_ProjectException');
        $this->project->SetValue($someStrangeValue);
        $textValue = "bigvalue!!!";
        $this->setExpectedException('C3op_Projects_ProjectException');
        $this->project->SetValue($textValue);
    }
    
    public function testCanChangeValueOfProject()
    {        
        $firstValue = "1,122,012.77";
        $newValue = "100000";
        $this->project->SetValue($firstValue);
        $this->assertEquals($this->project->getValue(), (float)$firstValue);
        $this->project->SetValue($newValue);
        $this->assertEquals($this->project->getValue(), (float)$newValue);
    }
 
}
