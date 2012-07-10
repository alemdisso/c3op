<?php

require_once ('ActionEventBase.php');

class C3op_Projects_ActionEvent extends C3op_Projects_ActionEventBase {
    
    function __construct($action, $id=0) {
        parent::__construct($action, $id);
    }
    
}