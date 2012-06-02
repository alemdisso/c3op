<?php

class C3op_Projects_ContractNatureTypes {
    
    private static $titles = array(
        C3op_Projects_ContractNatureConstants::NATURE_CONTRACT => "Contrato",
        C3op_Projects_ContractNatureConstants::NATURE_AGREEMENT => "Convênio",
        C3op_Projects_ContractNatureConstants::NATURE_PARTNERSHIP_TERM => "Termo de parceria",
        C3op_Projects_ContractNatureConstants::NATURE_DONATION => "Doação",
        C3op_Projects_ContractNatureConstants::NATURE_SPONSORSHIP => "Patrocínio",
        C3op_Projects_ContractNatureConstants::NATURE_COOPERATION => "Acordo de cooperação",
        C3op_Projects_ContractNatureConstants::NATURE_OTHER => "Outras",
    );
    
    public static function TitleForType($type)
    {
            switch ($type) {
                case C3op_Projects_ContractNatureConstants::NATURE_CONTRACT:
                case C3op_Projects_ContractNatureConstants::NATURE_AGREEMENT:
                case C3op_Projects_ContractNatureConstants::NATURE_PARTNERSHIP_TERM:
                case C3op_Projects_ContractNatureConstants::NATURE_DONATION:
                case C3op_Projects_ContractNatureConstants::NATURE_SPONSORSHIP:
                case C3op_Projects_ContractNatureConstants::NATURE_COOPERATION:
                case C3op_Projects_ContractNatureConstants::NATURE_OTHER:
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