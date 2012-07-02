<?php

class C3op_Projects_ActionStatusTypes {
    
    private static $status = array(
        C3op_Projects_ActionStatusConstants::STATUS_PLAN => "Em Planejamento",
        C3op_Projects_ActionStatusConstants::STATUS_CONTRACT => "Contratada",
        C3op_Projects_ActionStatusConstants::STATUS_DONE => "Realizada",
        C3op_Projects_ActionStatusConstants::STATUS_PAY => "Pagamento",
        C3op_Projects_ActionStatusConstants::STATUS_SETTLE => "Quitada",
        C3op_Projects_ActionStatusConstants::STATUS_ABORT => "Abortada",
    );
    
    public static function TitleForType($type)
    {
            switch ($type) {
                case C3op_Projects_ActionStatusConstants::STATUS_PLAN:
                case C3op_Projects_ActionStatusConstants::STATUS_CONTRACT:
                case C3op_Projects_ActionStatusConstants::STATUS_DONE:
                case C3op_Projects_ActionStatusConstants::STATUS_PAY:
                case C3op_Projects_ActionStatusConstants::STATUS_SETTLE:
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