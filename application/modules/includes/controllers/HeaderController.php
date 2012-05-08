<?php

class Includes_HeaderController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }


    public function includeMockAction()
    {
        /* Initialize model and retrieve data here */

        /* Initialize view and populate here */

        //saudacao_usuario
        //  logado: verdadeiro/falso
        //  nome_usuario: texto
        //  link_edita: texto
        $dadosPagina['saudacao_usuario']['logado'] = true;
        $dadosPagina['saudacao_usuario']['nome_usuario'] = "Fabiana Lizak";
        $dadosPagina['saudacao_usuario']['link_edita'] = "/edita-usuario/fabiana-lizak";

        $this->view->saudacaoUsuario = $dadosPagina['saudacao_usuario'];

        $this->render("include"); //use different view
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
            $dadosPagina['saudacao_usuario']['nome_usuario'] = $identity->nomeUsuario;
            $dadosPagina['saudacao_usuario']['link_edita'] = "/autenticacao/conta/edita?titulo=" . $identity->apelidoUsuario;
//            $dadosPagina['links_menu']['link_aulas'] = "/planejamento/aula/lista?titulo=" . $identity->apelidoUsuario;
            $dadosPagina['links_menu']['link_aulas'] = "/planejamento/aula/lista";

        } else {
            $dadosPagina['saudacao_usuario']['logado'] = false;
            $dadosPagina['saudacao_usuario']['nome_usuario'] = "";
            $dadosPagina['saudacao_usuario']['link_edita'] = "";
            $dadosPagina['links_menu']['link_aulas'] = "/planejamento/aula/lista";
        }

        $this->view->saudacaoUsuario = $dadosPagina['saudacao_usuario'];
        $this->view->linksMenu = $dadosPagina['links_menu'];

    }


}