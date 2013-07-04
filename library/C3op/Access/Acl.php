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
    $this->add(new Zend_Acl_Resource('c3op:finances'));
    $this->add(new Zend_Acl_Resource('c3op:finances.outlay'));
    $this->add(new Zend_Acl_Resource('c3op:finances.product'));
    $this->add(new Zend_Acl_Resource('c3op:finances.receivable'));
    $this->add(new Zend_Acl_Resource('c3op:projects'));
    $this->add(new Zend_Acl_Resource('c3op:projects.index'));
    $this->add(new Zend_Acl_Resource('c3op:projects.action'));
    $this->add(new Zend_Acl_Resource('c3op:projects.contract'));
    $this->add(new Zend_Acl_Resource('c3op:projects.project'));
    $this->add(new Zend_Acl_Resource('c3op:resources.material-supply'));
    $this->add(new Zend_Acl_Resource('c3op:resources.outside-service'));
    $this->add(new Zend_Acl_Resource('c3op:resources.responsible'));
    $this->add(new Zend_Acl_Resource('c3op:resources.team-member'));
    $this->add(new Zend_Acl_Resource('c3op:register'));
    $this->add(new Zend_Acl_Resource('c3op:register.contact'));
    $this->add(new Zend_Acl_Resource('c3op:register.institution'));
    $this->add(new Zend_Acl_Resource('c3op:register.linkage'));

    $this->allow(C3op_Access_RolesConstants::ROLE_UNKNOWN,       'c3op:auth.login', 'index');
    $this->allow(C3op_Access_RolesConstants::ROLE_UNKNOWN,       'c3op:auth.logout', 'index');

    $this->allow(C3op_Access_RolesConstants::ROLE_ADMINISTRATOR, 'c3op:finances.outlay', 'create');
    $this->allow(C3op_Access_RolesConstants::ROLE_ADMINISTRATOR, 'c3op:finances.outlay', 'edit');
    $this->allow(C3op_Access_RolesConstants::ROLE_ADMINISTRATOR, 'c3op:finances.outlay', 'notify');
    $this->deny(C3op_Access_RolesConstants::ROLE_CONTROLLER,     'c3op:finances.outlay');
    $this->allow(C3op_Access_RolesConstants::ROLE_COORDINATOR, 'c3op:finances.outlay', 'create');
    $this->allow(C3op_Access_RolesConstants::ROLE_DIRECTOR, 'c3op:finances.outlay', 'edit');
    $this->allow(C3op_Access_RolesConstants::ROLE_DIRECTOR, 'c3op:finances.outlay', 'create');
    $this->allow(C3op_Access_RolesConstants::ROLE_DIRECTOR, 'c3op:finances.outlay', 'notify');

    $this->allow(C3op_Access_RolesConstants::ROLE_ADMINISTRATOR,     'c3op:finances.product', 'detail');
    $this->deny(C3op_Access_RolesConstants::ROLE_CONTROLLER,     'c3op:finances.product', 'detail');

    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:finances.receivable', 'create');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:finances.receivable', 'edit');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:finances.receivable', 'notify');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:finances.receivable', 'detail');
    $this->deny(C3op_Access_RolesConstants::ROLE_CONTROLLER,     'c3op:finances.receivable');
    $this->allow(C3op_Access_RolesConstants::ROLE_COORDINATOR,   'c3op:finances.receivable');
    $this->allow(C3op_Access_RolesConstants::ROLE_DIRECTOR,      'c3op:finances.receivable');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:finances.receivable');

    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.index', 'index');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.index', 'all-projects');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.index', 'engagement');
    $this->allow(C3op_Access_RolesConstants::ROLE_COORDINATOR,   'c3op:projects.action', 'accept-receipt');
    $this->allow(C3op_Access_RolesConstants::ROLE_CONTROLLER,    'c3op:projects.action', 'acknowledge-receipt');
    $this->allow(C3op_Access_RolesConstants::ROLE_CONTROLLER,    'c3op:projects.action', 'acknowledge-start');
    $this->allow(C3op_Access_RolesConstants::ROLE_ADMINISTRATOR, 'c3op:projects.action', 'delivery-notify');
    $this->deny(C3op_Access_RolesConstants::ROLE_CONTROLLER,     'c3op:projects.action', 'delivery-notify');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.action', 'create');
    $this->allow(C3op_Access_RolesConstants::ROLE_CONTROLLER,    'c3op:projects.action', 'change-start');
    $this->allow(C3op_Access_RolesConstants::ROLE_CONTROLLER,    'c3op:projects.action', 'create-product');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.action', 'edit-product');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.action', 'detail');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.action', 'edit');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.action', 'populate-subordinated-to');
    $this->allow(C3op_Access_RolesConstants::ROLE_COORDINATOR,   'c3op:projects.action', 'reject-receipt');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.action', 'remove');
    $this->allow(C3op_Access_RolesConstants::ROLE_COORDINATOR,   'c3op:projects.action', 'budget-create');
    $this->allow(C3op_Access_RolesConstants::ROLE_COORDINATOR,   'c3op:projects.action', 'budget-forecast');

    $this->allow(C3op_Access_RolesConstants::ROLE_CONTROLLER,    'c3op:resources.responsible');
    $this->allow(C3op_Access_RolesConstants::ROLE_CONTROLLER,    'c3op:resources.responsible', 'create');
    $this->allow(C3op_Access_RolesConstants::ROLE_CONTROLLER,    'c3op:resources.responsible', 'assigned');
    $this->allow(C3op_Access_RolesConstants::ROLE_ADMINISTRATOR, 'c3op:resources.responsible', 'contract');
    $this->allow(C3op_Access_RolesConstants::ROLE_ADMINISTRATOR, 'c3op:resources.responsible', 'dismiss');
    $this->deny(C3op_Access_RolesConstants::ROLE_CONTROLLER,     'c3op:resources.responsible', 'contract');
    $this->deny(C3op_Access_RolesConstants::ROLE_CONTROLLER,     'c3op:resources.responsible', 'dismiss');

    $this->allow(C3op_Access_RolesConstants::ROLE_CONTROLLER,    'c3op:resources.material-supply');
    $this->allow(C3op_Access_RolesConstants::ROLE_CONTROLLER,    'c3op:resources.material-supply', 'create');
    $this->allow(C3op_Access_RolesConstants::ROLE_CONTROLLER,    'c3op:resources.material-supply', 'contract');
    $this->allow(C3op_Access_RolesConstants::ROLE_CONTROLLER,    'c3op:resources.material-supply', 'dismiss-material');
    $this->allow(C3op_Access_RolesConstants::ROLE_ADMINISTRATOR, 'c3op:resources.material-supply', 'contract');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:resources.material-supply', 'outlays');

    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.project', 'amend');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.project', 'create');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.project', 'detail');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.project', 'edit');
    $this->allow(C3op_Access_RolesConstants::ROLE_ADMINISTRATOR, 'c3op:projects.project', 'payables');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.project', 'receivables');
    $this->allow(C3op_Access_RolesConstants::ROLE_CONTROLLER,    'c3op:projects.project', 'unacknowledged');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.project', 'populate-responsible-at-client');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:projects.project', 'engagement');

    $this->allow(C3op_Access_RolesConstants::ROLE_ADMINISTRATOR, 'c3op:projects.contract', 'create');
    $this->allow(C3op_Access_RolesConstants::ROLE_ADMINISTRATOR, 'c3op:projects.contract', 'edit');
    $this->allow(C3op_Access_RolesConstants::ROLE_ADMINISTRATOR, 'c3op:projects.contract', 'detail');
    $this->deny(C3op_Access_RolesConstants::ROLE_CONTROLLER,     'c3op:projects.contract');

    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.contact', 'create');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.contact', 'remove');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.contact', 'edit');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.contact', 'detail');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.contact', 'index');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.contact', 'add-phone-number');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.contact', 'change-phone-number');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.contact', 'add-email');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.contact', 'change-email');

    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.institution', 'create');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.institution', 'edit');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.institution', 'index');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.institution', 'detail');

    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.linkage', 'create');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.linkage', 'edit');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.linkage', 'add-phone-number');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.linkage', 'change-phone-number');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.linkage', 'add-email');
    $this->allow(C3op_Access_RolesConstants::ROLE_ASSISTANT,     'c3op:register.linkage', 'change-email');

  }

}
