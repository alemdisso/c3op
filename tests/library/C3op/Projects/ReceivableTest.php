<?php

class ReceivableTest extends ControllerTestCase
{
    private $Receivable;
    public function setUp() {
        parent::setUp();
    }
    
    public function testThatCanCreateReceivableWithProjectAndPredictedDateAndValue()
    {            
        $someProjectId = 74;
        $someDate = '01-12-2012';
        $someValue = 1540020.77;
        $this->Receivable = new C3op_Projects_Receivable($someProjectId, $someDate, $someValue);
        $this->assertEquals($this->Receivable->getProject(), $someProjectId);
    }
    

}
