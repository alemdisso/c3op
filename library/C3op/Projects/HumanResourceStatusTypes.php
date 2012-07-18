<?php

class C3op_Projects_HumanResourceStatusTypes {
    
    private static $status = array(
        C3op_Projects_HumanResourceStatusConstants::STATUS_NIL => "Nulo",
        C3op_Projects_HumanResourceStatusConstants::STATUS_UNDEFINED => "Indefinido",
        C3op_Projects_HumanResourceStatusConstants::STATUS_FORESEEN => "Previsto",
        C3op_Projects_HumanResourceStatusConstants::STATUS_CONTRACTED => "Contratado",
        C3op_Projects_HumanResourceStatusConstants::STATUS_ACQUITTED => "Quitado",
        C3op_Projects_HumanResourceStatusConstants::STATUS_CANCEL => "Cancelado",
    );
    
    public static function TitleForType($type)
    {
            switch ($type) {
                case C3op_Projects_HumanResourceStatusConstants::STATUS_NIL:
                case C3op_Projects_HumanResourceStatusConstants::STATUS_UNDEFINED:
                case C3op_Projects_HumanResourceStatusConstants::STATUS_FORESEEN:
                case C3op_Projects_HumanResourceStatusConstants::STATUS_CONTRACTED:
                case C3op_Projects_HumanResourceStatusConstants::STATUS_ACQUITTED:
                case C3op_Projects_HumanResourceStatusConstants::STATUS_CANCEL:
                    return self::$status[$type];
                    break;
                
                default:
                    return "Tipo desconhecido";
                    break;
            }
    }
    
    public static function AllStatus()
    {
        return self::$status;        
    }   
}