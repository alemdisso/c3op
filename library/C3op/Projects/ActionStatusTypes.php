<?php

class C3op_Projects_ActionStatusTypes {
    
    private static $status = array(
        C3op_Projects_ActionStatusConstants::STATUS_NIL => "Nula",
        C3op_Projects_ActionStatusConstants::STATUS_PLAN => "Em Planejamento",
        C3op_Projects_ActionStatusConstants::STATUS_IN_EXECUTION => "Em Execução",
        C3op_Projects_ActionStatusConstants::STATUS_RECEIVED => "Recebida",
        C3op_Projects_ActionStatusConstants::STATUS_DONE => "Realizada",
        C3op_Projects_ActionStatusConstants::STATUS_DELIVERED => "Entregue",
        C3op_Projects_ActionStatusConstants::STATUS_COMPLETE => "Completa",
        C3op_Projects_ActionStatusConstants::STATUS_ABORT => "Abortada",
    );
    
    public static function TitleForType($type)
    {
            switch ($type) {
                case C3op_Projects_ActionStatusConstants::STATUS_NIL:
                case C3op_Projects_ActionStatusConstants::STATUS_PLAN:
                case C3op_Projects_ActionStatusConstants::STATUS_IN_EXECUTION:
                case C3op_Projects_ActionStatusConstants::STATUS_RECEIVED:
                case C3op_Projects_ActionStatusConstants::STATUS_DONE:
                case C3op_Projects_ActionStatusConstants::STATUS_DELIVERED:
                case C3op_Projects_ActionStatusConstants::STATUS_COMPLETE:
                case C3op_Projects_ActionStatusConstants::STATUS_ABORT:
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