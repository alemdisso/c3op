<?php

class C3op_Register_ContactTypes {
    
    private static $titles = array(
            C3op_Register_ContactConstants::CONTACT_GENERAL => "Geral",
            C3op_Register_ContactConstants::CONTACT_CLIENT => "Cliente",
            C3op_Register_ContactConstants::CONTACT_PARTNER => "Parceiro",
            C3op_Register_ContactConstants::CONTACT_ASSOCIATE => "Associado",
            C3op_Register_ContactConstants::CONTACT_CONTRACTOR => "Contratado",
            C3op_Register_ContactConstants::CONTACT_SUPPLIER => "Fornecedor",
    );
    
    public static function TitleForType($type)
    {
            switch ($type) {
                case C3op_Register_ContactConstants::CONTACT_GENERAL:
                case C3op_Register_ContactConstants::CONTACT_CLIENT:
                case C3op_Register_ContactConstants::CONTACT_PARTNER:
                case C3op_Register_ContactConstants::CONTACT_ASSOCIATE:
                case C3op_Register_ContactConstants::CONTACT_CONTRACTOR:
                case C3op_Register_ContactConstants::CONTACT_SUPPLIER:
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