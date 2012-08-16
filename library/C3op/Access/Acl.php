<?php

class C3op_Access_Acl extends Zend_Acl 
{
    
public function __construct() {
    
    $roles = C3op_Access_Roles::AllRoles();
    $previousRole = null;
    while (list($role, $label) = each($roles)) {
        if ($role != C3op_Access_RolesConstants::ROLE_SYSADMIN) {
            if ($previousRole === null) {
                $this->addRole(new Zend_Acl_Role($role));
            } else {
                $this->addRole(new Zend_Acl_Role($role), $previousRole);
            }
            $previousRole = $role;
        }
    }        
    $this->addRole(new Zend_Acl_Role(C3op_Access_RolesConstants::ROLE_SYSADMIN));
    $this->allow(C3op_Access_RolesConstants::ROLE_SYSADMIN);
    
    $this->add(new Zend_Acl_Resource('c3op:auth'));
    $this->add(new Zend_Acl_Resource('c3op:auth.login'));
    $this->add(new Zend_Acl_Resource('c3op:auth.logout'));
    $this->add(new Zend_Acl_Resource('c3op:auth.user'));
    $this->add(new Zend_Acl_Resource('c3op:register'));
    $this->add(new Zend_Acl_Resource('c3op:projects'));
    

    $this->allow(C3op_Access_RolesConstants::ROLE_UNKNOWN, 'c3op:auth.login', 'index');
    $this->allow(C3op_Access_RolesConstants::ROLE_UNKNOWN, 'c3op:auth.login', 'logout');
    $this->allow(C3op_Access_RolesConstants::ROLE_COORDINATOR, 'c3op:auth.user', 'create');
    //$this->allow(C3op_Access_RolesConstants::ROLE_USER, 'c3op:register.institution', 'index');
    $this->allow(C3op_Access_RolesConstants::ROLE_USER, 'c3op:register', 'index');
 
  }    
    
}
