<?php


class C3op_Access_Roles {
    
    private static $roles = array(
//        C3op_Access_RolesConstants::ROLE_UNKNOWN => "Desconhecido",
//        C3op_Access_RolesConstants::ROLE_GUEST => "Convidado",
        C3op_Access_RolesConstants::ROLE_USER => "Cadastrado",
        C3op_Access_RolesConstants::ROLE_ASSISTANT => "Assistente administrativo",
        C3op_Access_RolesConstants::ROLE_ADMINISTRATOR => "Administrador",
        C3op_Access_RolesConstants::ROLE_DIRECTOR => "Gestor",
        C3op_Access_RolesConstants::ROLE_COORDINATOR => "Coordenador",
        C3op_Access_RolesConstants::ROLE_DIRECTOR => "Diretor",
        C3op_Access_RolesConstants::ROLE_SYSADMIN => "Administrador do Sistema",
    );
    
    public static function TitleForRole($role)
    {
            switch ($role) {
                case C3op_Access_RolesConstants::ROLE_UNKNOWN:
                case C3op_Access_RolesConstants::ROLE_GUEST:
                case C3op_Access_RolesConstants::ROLE_USER:
                case C3op_Access_RolesConstants::ROLE_ASSISTANT:
                case C3op_Access_RolesConstants::ROLE_ADMINISTRATOR:
                case C3op_Access_RolesConstants::ROLE_DIRECTOR:
                case C3op_Access_RolesConstants::ROLE_COORDINATOR:
                case C3op_Access_RolesConstants::ROLE_DIRECTOR:
                case C3op_Access_RolesConstants::ROLE_SYSADMIN:
                    return self::$roles[$role];
                    break;
                
                default:
                    return "Papel desconhecido";
                    break;
            }
    }
    
    public static function AllRoles()
    {
        return self::$roles;        
    }   
}