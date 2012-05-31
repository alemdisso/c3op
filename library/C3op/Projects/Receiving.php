<?php

require_once ('ReceivingBase.php');

class C3op_Projects_Receiving extends C3op_Projects_ReceivingBase {
    
    function __construct($project, $predictedDate, $predictedValue, $id=0) {
        parent::__construct($project, $predictedDate, $predictedValue);
    }
}