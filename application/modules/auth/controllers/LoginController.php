<?php

class Auth_LoginController extends Zend_Controller_Action
{

    public function indexAction()
    {
        
        $layoutHelper = $this->_helper->getHelper('Layout');
        $layout = $layoutHelper->getLayoutInstance();

        $layout->title = "IETS";
        
        $layout->nestedLayout = 'login';
        
        $form = new C3op_Form_UserLogin;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $user = $form->process($postData);
                if ($user instanceOf C3op_Auth_User ) {
                    $session = new Zend_Session_Namespace('c3op.auth');
                    $session->user = $user;
                    $this->_redirect("/projects");
//                    if (isset($session->requestURL)) {
//                        $url = $session->requestURL;
//                        unset($session->requestURL);
//                        $this->_redirect($url);
//                    } else {
//                        $this->_helper->getHelper('FlashMessenger')
//                            ->addMessage('You were successfully logged in.');
//                        $this->_redirect('/projects');
//                    }

                }

            } else {
                $this->view->form = $form;
                $this->view->message = 'You could not be logged in. Please try again.';
                return $this->render('login');
                
            }
                
        }
        
    }
    
    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        Zend_Session::destroy();
        $this->_redirect('/auth/login');
    }    
    
    
}