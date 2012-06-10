<?php

class C3op_Register_RelationshipTypes {
    
    private static $titles = array(
            C3op_Register_InstitutionConstants::RELATIONSHIP_GENERAL => "Geral",
            C3op_Register_InstitutionConstants::RELATIONSHIP_CLIENT => "Cliente",
            C3op_Register_InstitutionConstants::RELATIONSHIP_PARTNER => "Parceiro",
            C3op_Register_InstitutionConstants::RELATIONSHIP_SUPPLIER => "Fornecedor",
            C3op_Register_InstitutionConstants::RELATIONSHIP_CONTRACTING => "Contratante",
    );
    
    public static function TitleForType($type)
    {
            switch ($type) {
                case C3op_Register_InstitutionConstants::RELATIONSHIP_GENERAL:
                case C3op_Register_InstitutionConstants::RELATIONSHIP_CLIENT:
                case C3op_Register_InstitutionConstants::RELATIONSHIP_PARTNER:
                case C3op_Register_InstitutionConstants::RELATIONSHIP_SUPPLIER:
                case C3op_Register_InstitutionConstants::RELATIONSHIP_CONTRACTING:
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