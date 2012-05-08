<?php
class IndexController extends Zend_Controller_Action
{
  public function init()
  {
      /* Initialize action controller here */
  }

  public function indexAction()
  {
      // action body
       throw new Zend_Exception('some error occured...');
  }
}

