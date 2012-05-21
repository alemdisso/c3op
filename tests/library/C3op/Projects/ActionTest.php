<?php

class ActionTest extends ControllerTestCase
{
    private $action;
    public function setUp() {
        parent::setUp();
    }
    
    public function testThatActionIsCreatedWithProjectRelated()
    {            
        $someProjectId = 74;
        $this->action = new C3op_Projects_Action($someProjectId);
        $this->assertEquals($this->action->getProject(), $someProjectId);
    }
    
    public function testIfCanSetMilestoneStatus()
    {
        $someProjectId = 67;
        $this->action = new C3op_Projects_Action($someProjectId);
        $this->action->SetMilestone(1);
        $this->assertEquals($this->action->getMilestone(), 1);
        $this->action->SetMilestone(0);
        $this->assertEquals($this->action->getMilestone(), 0);
        
    }

    public function testIfCanSetIfIsRequirementForReceivingOrNot()
    {
        $someProjectId = 80;
        $this->action = new C3op_Projects_Action($someProjectId);
        $this->action->SetRequirementForReceiving(1);
        $this->assertEquals($this->action->GetRequirementForReceiving(), 1);
        $this->action->SetRequirementForReceiving(0);
        $this->assertEquals($this->action->GetRequirementForReceiving(), 0);
        
    }

}
