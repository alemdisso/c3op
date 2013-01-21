<?php

class C3op_Resources_TeamMemberStatusTypes {

    private $status = array();


    public function __construct() {
        $this->status = array(
        C3op_Resources_TeamMemberStatusConstants::STATUS_NIL => "Nulo",
        C3op_Resources_TeamMemberStatusConstants::STATUS_UNDEFINED => "Indefinido",
        C3op_Resources_TeamMemberStatusConstants::STATUS_FORESEEN => "Previsto",
        C3op_Resources_TeamMemberStatusConstants::STATUS_CONTRACTED => "Contratado",
        C3op_Resources_TeamMemberStatusConstants::STATUS_ACQUITTED => "Quitado",
        C3op_Resources_TeamMemberStatusConstants::STATUS_CANCEL => "Cancelado",
        );
    }

    public function TitleForType($type)
    {
            switch ($type) {
                case C3op_Resources_TeamMemberStatusConstants::STATUS_NIL:
                case C3op_Resources_TeamMemberStatusConstants::STATUS_UNDEFINED:
                case C3op_Resources_TeamMemberStatusConstants::STATUS_FORESEEN:
                case C3op_Resources_TeamMemberStatusConstants::STATUS_CONTRACTED:
                case C3op_Resources_TeamMemberStatusConstants::STATUS_ACQUITTED:
                case C3op_Resources_TeamMemberStatusConstants::STATUS_CANCEL:
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