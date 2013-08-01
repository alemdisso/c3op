<?php
class IndexController extends Zend_Controller_Action
{
    public function preDispatch()
    {
        try {
            $checker = new C3op_Access_PrivilegeChecker();
        } catch (Exception $e) {
            throw $e;
        }

        $this->view->pageTitle = "";


    }

    public function postDispatch()
    {
        if (isset($this->view->pageTitle)) {
            $trail = new C3op_Util_Breadcrumb();
            $breadcrumb = $trail->add($this->view->pageTitle, $this->getRequest()->getRequestUri());
            $this->_helper->layout()->getView()->headTitle($this->view->pageTitle);
        }
    }

  public function init()
  {
        $this->db = Zend_Registry::get('db');
  }

  public function indexAction()
  {
        $this->view->pageTitle = $this->view->translate("#Dashboard");


  }


}

