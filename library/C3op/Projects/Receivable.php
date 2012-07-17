<?php

require_once ('ReceivableBase.php');

class C3op_Projects_Receivable extends C3op_Projects_ReceivableBase {
    
    function __construct($project, $predictedDate, $predictedValue, $id=0) {
        parent::__construct($project, $predictedDate, $predictedValue);
    }
}