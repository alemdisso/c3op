<?php

class C3op_Auth_UserCanSeeFinances
{


    private $user;

    function __construct(C3op_Auth_User $user)
    {
            $this->user = $user;
    }

    public function can()
    {

        $role = $this->user->GetRole();
        if (($role == C3op_Access_RolesConstants::ROLE_SYSADMIN)
                || ($role == C3op_Access_RolesConstants::ROLE_DIRECTOR)
                || ($role == C3op_Access_RolesConstants::ROLE_COORDINATOR)
                || ($role == C3op_Access_RolesConstants::ROLE_ADMINISTRATOR)
                || ($role == C3op_Access_RolesConstants::ROLE_ASSISTANT)
                ) {
            return true;
        } else {
            return false;
        }

    }


}
