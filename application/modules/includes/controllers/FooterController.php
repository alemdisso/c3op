<?php

require_once APPLICATION_PATH . "/modules/includes/models/MediadorIncludes.php";

class Includes_FooterController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }



    public function includeAction()
    {
        /* Initialize model and retrieve data here */
        
        $reqIncludes = new MediadorIncludes();

        
    }


}