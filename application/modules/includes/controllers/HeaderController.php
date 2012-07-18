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
            $action = $this->getRequest()->getActionName();
            $this->_helper->layout->setLayout('login');
            $session = new Zend_Session_Namespace('c3op.auth');
            if ($action != "include-login") {
                $session->requestURL = $url;
                $this->_redirect('/auth/login');
            }
        }
    }


    public function includeAction()
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

            $dadosPagina['saudacao_usuario']['logado'] = true;
            $dadosPagina['saudacao_usuario']['nome_usuario'] = $identity->GetLogin();
            $dadosPagina['saudacao_usuario']['link_edita'] = "/autenticacao/conta/edita?titulo=" . $identity->GetId();
//            $dadosPagina['links_menu']['link_aulas'] = "/planejamento/aula/lista?titulo=" . $identity->apelidoUsuario;
            $dadosPagina['links_menu']['link_aulas'] = "/projects";

        } else {
            $dadosPagina['saudacao_usuario']['logado'] = false;
            $dadosPagina['saudacao_usuario']['nome_usuario'] = "";
            $dadosPagina['saudacao_usuario']['link_edita'] = "";
            $dadosPagina['links_menu']['link_aulas'] = "/projects";
        }

        $this->view->saudacaoUsuario = $dadosPagina['saudacao_usuario'];
        $this->view->linksMenu = $dadosPagina['links_menu'];

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

            $dadosPagina['saudacao_usuario']['logado'] = true;
            $dadosPagina['saudacao_usuario']['nome_usuario'] = $identity->nomeUsuario;
            $dadosPagina['saudacao_usuario']['link_edita'] = "/autenticacao/conta/edita?titulo=" . $identity->apelidoUsuario;
//            $dadosPagina['links_menu']['link_aulas'] = "/planejamento/aula/lista?titulo=" . $identity->apelidoUsuario;
            $dadosPagina['links_menu']['link_aulas'] = "/projects";

        } else {
            $dadosPagina['saudacao_usuario']['logado'] = false;
            $dadosPagina['saudacao_usuario']['nome_usuario'] = "";
            $dadosPagina['saudacao_usuario']['link_edita'] = "";
            $dadosPagina['links_menu']['link_aulas'] = "/projects";
        }

        $this->view->saudacaoUsuario = $dadosPagina['saudacao_usuario'];
        $this->view->linksMenu = $dadosPagina['links_menu'];

    }


}