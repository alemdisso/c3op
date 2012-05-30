<?php

class ReceivingTest extends ControllerTestCase
{
    private $receiving;
    public function setUp() {
        parent::setUp();
    }
    
    public function testThatCanCreateReceivingWithProjectAndPredictedDateAndValue()
    {            
        $someProjectId = 74;
        $someDate = '01-12-2012';
        $someValue = 1540020.77;
        $this->receiving = new C3op_Projects_Receiving($someProjectId, $someDate, $someValue);
        $this->assertEquals($this->receiving->getProject(), $someProjectId);
    }
    

}
