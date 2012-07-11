<?php

class C3op_Projects_ActionEventTypes {
    
    private static $types = array(
        C3op_Projects_ActionEventConstants::EVENT_NIL => "Nula",
        C3op_Projects_ActionEventConstants::EVENT_PLANNING => "Entrando em planejamento",
        C3op_Projects_ActionEventConstants::EVENT_CONTRACT_RESOURCE => "Confirma contratação de recurso",
        C3op_Projects_ActionEventConstants::EVENT_BEGIN_EXECUTION => "Entrando Em execução",
        C3op_Projects_ActionEventConstants::EVENT_CONFIRM_REALIZATION => "Confirma Realização",
        C3op_Projects_ActionEventConstants::EVENT_ACKNOWLEDGE_DELIVERY => "Registra Entrega",
        C3op_Projects_ActionEventConstants::EVENT_DISMISS_RESOURCE => "Dispensa recurso",
        C3op_Projects_ActionEventConstants::EVENT_HALT_EXECUTION => "Suspende execução",
        C3op_Projects_ActionEventConstants::EVENT_REJECT_DELIVERY => "Reprova entrega",
        C3op_Projects_ActionEventConstants::EVENT_CANCEL_ACTION => "Cancela ação",
    );
    
    public static function TitleForType($type)
    {
            switch ($type) {
                case C3op_Projects_ActionEventConstants::EVENT_NIL:
                case C3op_Projects_ActionEventConstants::EVENT_PLANNING:
                case C3op_Projects_ActionEventConstants::EVENT_CONTRACT_RESOURCE:
                case C3op_Projects_ActionEventConstants::EVENT_BEGIN_EXECUTION:
                case C3op_Projects_ActionEventConstants::EVENT_CONFIRM_REALIZATION:
                case C3op_Projects_ActionEventConstants::EVENT_ACKNOWLEDGE_DELIVERY:
                case C3op_Projects_ActionEventConstants::EVENT_DISMISS_RESOURCE:
                case C3op_Projects_ActionEventConstants::EVENT_HALT_EXECUTION:
                case C3op_Projects_ActionEventConstants::EVENT_REJECT_DELIVERY:
                case C3op_Projects_ActionEventConstants::EVENT_CANCEL_ACTION:
                    return self::$types[$type];
                    break;
                
                default:
                    return "Tipo desconhecido";
                    break;
            }
    }
    
    public static function AllTypes()
    {
        return self::$types;        
    }   
}