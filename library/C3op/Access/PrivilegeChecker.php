<?php

class C3op_Access_PrivilegeChecker
{
    private $user;
    private $acl;

    public function __construct() {
        $this->user = Zend_Registry::get('user');
        $this->acl = Zend_Registry::get('acl');
        
        if (!empty($this->user)) {
            $role = $this->user->GetRole();
        } else {
            $role = C3op_Access_RolesConstants::ROLE_UNKNOWN;
        }
        
        $controllerInstance = Zend_Controller_Front::getInstance();
        $request = $controllerInstance->getRequest();
        $module = $controllerInstance->getRequest()->getModuleName();
        $action = $controllerInstance->getRequest()->getActionName();
        $controller = $controllerInstance->getRequest()->getControllerName();
        $moduleLevel = "c3op:" . $module;
        $controllerLevel = $moduleLevel . "." . $controller;
        
        if ($this->acl->has($controllerLevel)) {
            $resource = $controllerLevel;
        } else {
            $resource = $moduleLevel;
        }
        
        
        if ($module != 'auth' || $controller != 'login') {
            if ($this->acl->has($resource) && !$this->acl->isAllowed($role, $resource, $action)) {
                $request->setModuleName("auth");
                $request->setControllerName("login");
                $request->setActionName("index");
                //$request->setParam('authPage', 'login');
                throw new Exception('Access denied. ' . $action . ' at ' . $resource . '::' . $role);
            }
        }
        

    }    
    
}
