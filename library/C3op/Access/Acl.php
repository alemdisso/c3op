<?php

class C3op_Access_Acl extends Zend_Acl
{

public function __construct() {

    $rolesObj = new C3op_Access_Roles();
    $roles = $rolesObj->AllRoles();
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
    $this->add(new Zend_Acl_Resource('c3op:register.contact'));
    $this->add(new Zend_Acl_Resource('c3op:register.institution'));
    $this->add(new Zend_Acl_Resource('c3op:register.linkage'));
    $this->add(new Zend_Acl_Resource('c3op:projects'));
    $this->add(new Zend_Acl_Resource('c3op:projects.action'));
    $this->add(new Zend_Acl_Resource('c3op:projects.team-member'));
    $this->add(new Zend_Acl_Resource('c3op:projects.outlay'));
    $this->add(new Zend_Acl_Resource('c3op:projects.project'));
    $this->add(new Zend_Acl_Resource('c3op:projects.receivable'));


    $this->allow(C3op_Access_RolesConstants::ROLE_UNKNOWN,       'c3op:auth.login', 'index');
    $this->allow(C3op_Access_RolesConstants::ROLE_UNKNOWN,       'c3op:auth.logout', 'index');

    $this->allow(C3op_Access_RolesConstants::ROLE_COORDINATOR,   'c3op:projects.action', 'accept-receipt');
    $this->allow(C3op_Access_RolesConstants::ROLE_COORDINATOR,   'c3op:projects.action', 'acknowledge-receipt');
    $this->allow(C3op_Access_RolesConstants::ROLE_CONTROLLER,    'c3op:projects.action', 'acknowledge-start');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.action', 'create');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.action', 'detail');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.action', 'edit');
    $this->allow(C3op_Access_RolesConstants::ROLE_ADMINISTRATOR, 'c3op:projects.action', 'tree');
    $this->allow(C3op_Access_RolesConstants::ROLE_COORDINATOR,   'c3op:projects.action', 'reject-receipt');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.action', 'success');

    $this->allow(C3op_Access_RolesConstants::ROLE_COORDINATOR,   'c3op:projects.team-member');
    $this->allow(C3op_Access_RolesConstants::ROLE_COORDINATOR,   'c3op:projects.team-member', 'create');
    $this->allow(C3op_Access_RolesConstants::ROLE_COORDINATOR,   'c3op:projects.team-member', 'contract');
    $this->allow(C3op_Access_RolesConstants::ROLE_COORDINATOR,   'c3op:projects.team-member', 'dismiss-contact');
    $this->allow(C3op_Access_RolesConstants::ROLE_ADMINISTRATOR, 'c3op:projects.team-member', 'contract');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.team-member', 'outlays');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.team-member', 'success');

    $this->allow(C3op_Access_RolesConstants::ROLE_ADMINISTRATOR, 'c3op:projects.outlay', 'create');
    $this->allow(C3op_Access_RolesConstants::ROLE_ADMINISTRATOR, 'c3op:projects.outlay', 'success');

    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.project', 'create');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.project', 'detail');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.project', 'edit');
    $this->allow(C3op_Access_RolesConstants::ROLE_ADMINISTRATOR, 'c3op:projects.project', 'payables');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.project', 'receivables');
    $this->allow(C3op_Access_RolesConstants::ROLE_CONTROLLER,    'c3op:projects.project', 'unacknowledged');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.project', 'populate-responsible-at-client');

    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.receivable', 'create');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.receivable', 'edit');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.receivable', 'success');

    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.contact', 'create');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.contact', 'edit');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.contact', 'detail');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.contact', 'index');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.contact', 'success');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.contact', 'add-phone-number');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.contact', 'change-phone-number');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.contact', 'add-email');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.contact', 'change-email');

    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.institution', 'create');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.institution', 'edit');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.institution', 'index');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.institution', 'success');

    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.linkage', 'create');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.linkage', 'edit');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.linkage', 'success');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.linkage', 'add-phone-number');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.linkage', 'change-phone-number');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.linkage', 'add-email');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.linkage', 'change-email');

  }

}
