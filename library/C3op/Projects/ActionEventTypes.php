<?php

class C3op_Projects_ActionEventTypes {
    
    private static $types = array(
        C3op_Projects_ActionEventConstants::EVENT_PLANNING => "Entrando em planejamento",
        C3op_Projects_ActionEventConstants::EVENT_PLANNED_BEGIN_DATE_CHANGE => "Mudança da data de início prevista",
        C3op_Projects_ActionEventConstants::EVENT_PLANNED_FINISH_DATE_CHANGE => "Mudança da data de término prevista",
        C3op_Projects_ActionEventConstants::EVENT_CONTRACT_RESOURCE => "Confirma contratação de recurso",
        C3op_Projects_ActionEventConstants::EVENT_BEGIN_EXECUTION => "Entrando em execução",
        C3op_Projects_ActionEventConstants::EVENT_BEGIN_AUTOMATICALLY => "Entrando automaticamente em execução",
        C3op_Projects_ActionEventConstants::EVENT_BEGIN_ACKNOWLEDGMENT => "Confirmando início de execução",
        C3op_Projects_ActionEventConstants::EVENT_ACKNOWLEDGE_RECEIPT => "Registra recebimento",
        C3op_Projects_ActionEventConstants::EVENT_CONFIRM_REALIZATION => "Confirma realização",
        C3op_Projects_ActionEventConstants::EVENT_REGISTER_DELIVERY => "Registra entrega",
        C3op_Projects_ActionEventConstants::EVENT_COMPLETE_ACTION => "Completa ação",
        C3op_Projects_ActionEventConstants::EVENT_DISMISS_RESOURCE => "Dispensa recurso",
        C3op_Projects_ActionEventConstants::EVENT_HALT_EXECUTION => "Suspende execução",
        C3op_Projects_ActionEventConstants::EVENT_REJECT_DELIVERY => "Reprova entrega",
        C3op_Projects_ActionEventConstants::EVENT_CANCEL_ACTION => "Cancela ação",
    );
    
    public static function TitleForType($type)
    {
            switch ($type) {
                case C3op_Projects_ActionEventConstants::EVENT_PLANNING:
                case C3op_Projects_ActionEventConstants::EVENT_PLANNED_BEGIN_DATE_CHANGE:
                case C3op_Projects_ActionEventConstants::EVENT_PLANNED_FINISH_DATE_CHANGE:
                case C3op_Projects_ActionEventConstants::EVENT_CONTRACT_RESOURCE:
                case C3op_Projects_ActionEventConstants::EVENT_BEGIN_EXECUTION:
                case C3op_Projects_ActionEventConstants::EVENT_BEGIN_AUTOMATICALLY:
                case C3op_Projects_ActionEventConstants::EVENT_BEGIN_ACKNOWLEDGMENT:
                case C3op_Projects_ActionEventConstants::EVENT_ACKNOWLEDGE_RECEIPT:
                case C3op_Projects_ActionEventConstants::EVENT_CONFIRM_REALIZATION:
                case C3op_Projects_ActionEventConstants::EVENT_REGISTER_DELIVERY:
                case C3op_Projects_ActionEventConstants::EVENT_COMPLETE_ACTION:
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