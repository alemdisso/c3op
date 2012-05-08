<?php

require_once ('IMediadorIncludes.php');

class MediadorIncludes implements IMediadorIncludes {


    function __construct() {

        $this->db = Zend_Registry::get('db');

    }
    
}