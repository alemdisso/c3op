<?php

class Auth_LogoutController extends Zend_Controller_Action
{

    public function indexAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        Zend_Session::destroy();
        $this->_redirect('/auth/login');
    }    
    
    
}