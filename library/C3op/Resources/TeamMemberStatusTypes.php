<?php

class C3op_Resources_TeamMemberStatusTypes {

    private $status = array();


    public function __construct() {
        $this->status = array(
        C3op_Resources_TeamMemberStatusConstants::STATUS_NIL => _("#Nil"),
        C3op_Resources_TeamMemberStatusConstants::STATUS_UNDEFINED => _("#Undefined"),
        C3op_Resources_TeamMemberStatusConstants::STATUS_FORESEEN => _("#Foreseen"),
        C3op_Resources_TeamMemberStatusConstants::STATUS_CONTRACTED => _("#Contracted"),
        C3op_Resources_TeamMemberStatusConstants::STATUS_ACQUITTED => _("#Acquitted"),
        C3op_Resources_TeamMemberStatusConstants::STATUS_DISMISSED => _("#Dismissed"),
        C3op_Resources_TeamMemberStatusConstants::STATUS_CANCEL => _("#Canceled"),
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
                case C3op_Resources_TeamMemberStatusConstants::STATUS_DISMISSED:
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