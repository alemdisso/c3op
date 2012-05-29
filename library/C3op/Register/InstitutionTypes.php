<?php

class C3op_Register_InstitutionTypes {
    
    private static $titles = array(
            C3op_Register_InstitutionConstants::INSTITUTION_PUBLIC => "Poder público",
            C3op_Register_InstitutionConstants::INSTITUTION_PRIVATE => "Entidade Privada",
            C3op_Register_InstitutionConstants::INSTITUTION_RESEARCH => "Instituto de pesquisa",
            C3op_Register_InstitutionConstants::INSTITUTION_NGO => "Organização Não Governamental",
            C3op_Register_InstitutionConstants::INSTITUTION_PERSONAL => "Particular",
    );
    
    public static function TitleForType($type)
    {
            switch ($type) {
                case C3op_Register_InstitutionConstants::INSTITUTION_PUBLIC:
                case C3op_Register_InstitutionConstants::INSTITUTION_PRIVATE:
                case C3op_Register_InstitutionConstants::INSTITUTION_RESEARCH:
                case C3op_Register_InstitutionConstants::INSTITUTION_NGO:
                case C3op_Register_InstitutionConstants::INSTITUTION_PERSONAL:
                    return self::$titles[$type];
                    break;
                
                default:
                    return "Tipo desconhecido";
                    break;
            }
    }
    
    public static function AllTitles()
    {
        return self::$titles;        
    }   
}