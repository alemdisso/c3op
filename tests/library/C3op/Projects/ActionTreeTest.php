<?php

class ActionTreeTest extends ControllerTestCase
{
    private $action;
    private $actionMapper;
    public function setUp() {
        parent::setUp();
        $db = Zend_Registry::get('db');
        $this->actionMapper = new C3op_Projects_ActionMapper($db);        
    }
    
//    public function testThatANewActionHasEmptyTree()
//    {            
//        $someProjectId = 36;
//        $this->action = new C3op_Projects_Action($someProjectId);
//        $obj = new C3op_Projects_ActionTree();
//        $tree = $obj->retrieveTree($this->action, $this->actionMapper);
//        $this->assertEquals(0, count($tree));
//    }    
//    public function testIfCanSetMilestoneStatus()
//    {
//        $someProjectId = 67;
//        $this->action = new C3op_Projects_Action($someProjectId);
//        $this->action->SetMilestone(1);
//        $this->assertEquals($this->action->getMilestone(), 1);
//        $this->action->SetMilestone(0);
//        $this->assertEquals($this->action->getMilestone(), 0);
//        
//    }
//
//    public function testIfCanSetIfIsRequirementForReceivingOrNot()
//    {
//        $someProjectId = 80;
//        $this->action = new C3op_Projects_Action($someProjectId);
//        $this->action->SetRequirementForReceiving(1);
//        $this->assertEquals($this->action->GetRequirementForReceiving(), 1);
//        $this->action->SetRequirementForReceiving(0);
//        $this->assertEquals($this->action->GetRequirementForReceiving(), 0);
//        
//    }

}
