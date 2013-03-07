<?php

class C3op_Resources_MaterialSupplyStatusTypes {

    private $status = array();


    public function __construct() {
        $this->status = array(
        C3op_Resources_MaterialSupplyStatusConstants::STATUS_NIL => "Nulo",
        C3op_Resources_MaterialSupplyStatusConstants::STATUS_UNDEFINED => "Indefinido",
        C3op_Resources_MaterialSupplyStatusConstants::STATUS_FORESEEN => "Previsto",
        C3op_Resources_MaterialSupplyStatusConstants::STATUS_CONTRACTED => "Contratado",
        C3op_Resources_MaterialSupplyStatusConstants::STATUS_ACQUITTED => "Quitado",
        C3op_Resources_MaterialSupplyStatusConstants::STATUS_CANCEL => "Cancelado",
        );
    }

    public function TitleForType($type)
    {
            switch ($type) {
                case C3op_Resources_MaterialSupplyStatusConstants::STATUS_NIL:
                case C3op_Resources_MaterialSupplyStatusConstants::STATUS_UNDEFINED:
                case C3op_Resources_MaterialSupplyStatusConstants::STATUS_FORESEEN:
                case C3op_Resources_MaterialSupplyStatusConstants::STATUS_CONTRACTED:
                case C3op_Resources_MaterialSupplyStatusConstants::STATUS_ACQUITTED:
                case C3op_Resources_MaterialSupplyStatusConstants::STATUS_CANCEL:
                    return $this->status[$type];
                    break;

                default:
                    return _("#Unknown type");
                    break;
            }
    }

    public function AllStatus()
    {
        return $this->status;
    }
}