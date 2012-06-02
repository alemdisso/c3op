<?php

class C3op_Projects_ProjectStatusTypes {
    
    private static $titles = array(
        C3op_Projects_ProjectStatusConstants::STATUS_PROSPECTING => "Prospecção",
        C3op_Projects_ProjectStatusConstants::STATUS_PLANNING => "Planejamento",
        C3op_Projects_ProjectStatusConstants::STATUS_PROPOSAL => "Proposta",
        C3op_Projects_ProjectStatusConstants::STATUS_EXECUTION => "Execução",
        C3op_Projects_ProjectStatusConstants::STATUS_ACCOUNTABILITY => "Prestação de contas",
        C3op_Projects_ProjectStatusConstants::STATUS_CANCELED => "Cancelado",
        C3op_Projects_ProjectStatusConstants::STATUS_SUSPENDED => "Suspenso",
    );
    
    public static function TitleForType($type)
    {
            switch ($type) {
                case C3op_Projects_ProjectStatusConstants::STATUS_PROSPECTING:
                case C3op_Projects_ProjectStatusConstants::STATUS_PLANNING:
                case C3op_Projects_ProjectStatusConstants::STATUS_PROPOSAL:
                case C3op_Projects_ProjectStatusConstants::STATUS_EXECUTION:
                case C3op_Projects_ProjectStatusConstants::STATUS_ACCOUNTABILITY:
                case C3op_Projects_ProjectStatusConstants::STATUS_CANCELED:
                case C3op_Projects_ProjectStatusConstants::STATUS_SUSPENDED:
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