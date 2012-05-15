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
 
}
