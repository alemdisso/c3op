<?php

class Includes_HeaderController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function preDispatch()
    {
        // check if user is authenticated
        // if not, redirect to login page

        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $url = $this->getRequest()->getRequestUri();
            $actionName = $this->getRequest()->getActionName();
            $this->_helper->layout->setLayout('login');
            $session = new Zend_Session_Namespace('c3op.auth');
            if ($actionName != "include-login") {
                $session->requestURL = $url;
                $this->_redirect('/auth/login');
            }
        }
    }


    public function includeAction()
    {
        $pageData = Array();

        $controller = $this->getFrontController();
        $moduleName = $controller->getParam('outerModule');

        $user = Zend_Registry::get('user');

        $navigationTabs = array();

        $test = new C3op_Access_UserCanSeeFinances($user);
        if ($test->can()) {
            $navigationTabs['finances']= array(
                    'url' => '/finances',
                    'label' => $this->view->translate('#Finances'),
                    'active' => ($moduleName == 'finances' ? true : false),
                    'moduleClass' => 'menufinances',
                );
        }
        $navigationTabs['projects']= array(
                'url' => '/projects',
                'label' => $this->view->translate('#Projects'),
                'active' => ($moduleName == 'projects' ? true : false),
                'moduleClass' => '',
            );
        $navigationTabs['register']= array(
                'url' => '/register',
                'label' => $this->view->translate('#Register'),
                'active' => ($moduleName == 'register' ? true : false),
                'moduleClass' => 'menuregister',
            );

        $auth = Zend_Auth::getInstance();

        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity(); //Identity exists; get it

            $pageData['loggedIn'] = true;
            $pageData['userName'] = $identity->GetLogin();
            $pageData['id'] = $identity->GetId();

            $trail = new C3op_Util_Breadcrumb();

            $crumbs = $trail->getCrumbs();

            $breadcrumb = array();

            $totalCrumbs = count($crumbs);
            $countCrumbs = 0;
            $maxCrumbs = 6;


            foreach ($crumbs as $uri => $label) {
                $countCrumbs++;
                if ($countCrumbs > ($totalCrumbs - $maxCrumbs)) {
                    $anArray = explode("/", $uri);
                    $module="";
                    if (count($anArray) > 1) {
                        $module = $anArray[1];
                    }
                    if ($label != "") {
                        $breadcrumb[] = array(
                            'uri' => $uri,
                            'label' => $label,
                            'module' => $module,
                        );
                    }
                }
            }


            $pageData['breadcrumb']=$breadcrumb;




        } else {
            $pageData['loggedIn'] = false;
            $pageData['userName'] = "";
            $pageData['id'] = null;
        }

        $pageData['navigationTabs'] = $navigationTabs;
        $this->view->pageData = $pageData;
    }

    public function includeLoginAction()
    {

        /* Initialize model and retrieve data here */

        $dadosPagina = Array();

        /* Initialize view and populate here */

        //saudacao_usuario
        //  logado: verdadeiro/falso
        //  nome_usuario: texto
        //  link_edita: texto
        $auth = Zend_Auth::getInstance();

        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity(); //Identity exists; get it

            $pageData['loggedIn'] = true;
            $pageData['userName'] = $identity->GetLogin();
            $pageData['id'] = $identity->GetId();

        } else {
            $pageData['loggedIn'] = false;
            $pageData['userName'] = "";
            $pageData['id'] = null;
        }

        //$pageData['navigationTabs'] = $navigationTabs;
        $this->view->pageData = $pageData;

    }



}