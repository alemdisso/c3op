<?php

require_once ('ActionBase.php');

class C3op_Projects_Action extends C3op_Projects_ActionBase {
    
    private $actionMapper;
    
    
    function __construct($project, $id=0) {
        parent::__construct($project, $id);
    }
    
}