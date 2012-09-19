<?php


class C3op_Access_Roles {

    private $roles = array();

    public function __construct() {
        $this->roles = array(
            C3op_Access_RolesConstants::ROLE_UNKNOWN => _("#Unknown"), //Desconhecido
            C3op_Access_RolesConstants::ROLE_GUEST => _("#Guest"), //Convidado
            C3op_Access_RolesConstants::ROLE_USER => _("#User"), //Cadastrado
            C3op_Access_RolesConstants::ROLE_ASSISTANT => _("#Assistant"), //Assistente administrativo
            C3op_Access_RolesConstants::ROLE_ADMINISTRATOR => _("#Administrator"), //Administrador
            C3op_Access_RolesConstants::ROLE_CONTROLLER => _("#Controller"), //Gestor
            C3op_Access_RolesConstants::ROLE_COORDINATOR => _("#Coordinator"), //Coordenador
            C3op_Access_RolesConstants::ROLE_DIRECTOR => _("#Director"), //Diretor
            C3op_Access_RolesConstants::ROLE_SYSADMIN => _("#System administrator"), //"Administrador do Sistema"
        );

    }

    public function TitleForRole($role)
    {
            switch ($role) {
                case C3op_Access_RolesConstants::ROLE_UNKNOWN:
                case C3op_Access_RolesConstants::ROLE_GUEST:
                case C3op_Access_RolesConstants::ROLE_USER:
                case C3op_Access_RolesConstants::ROLE_ASSISTANT:
                case C3op_Access_RolesConstants::ROLE_ADMINISTRATOR:
                case C3op_Access_RolesConstants::ROLE_CONTROLLER:
                case C3op_Access_RolesConstants::ROLE_COORDINATOR:
                case C3op_Access_RolesConstants::ROLE_DIRECTOR:
                case C3op_Access_RolesConstants::ROLE_SYSADMIN:
                    return $this->roles[$role];
                    break;

                default:
                    return "Papel desconhecido";
                    break;
            }
    }

    public function AllRoles()
    {
        return $this->roles;
    }
}