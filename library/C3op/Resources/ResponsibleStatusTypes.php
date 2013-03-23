<?php

class C3op_Resources_ResponsibleStatusTypes {

    private $status = array();


    public function __construct() {
        $this->status = array(
        C3op_Resources_ResponsibleStatusConstants::STATUS_NIL => _("#Nil"),
        C3op_Resources_ResponsibleStatusConstants::STATUS_UNDEFINED => _("#Undefined"),
        C3op_Resources_ResponsibleStatusConstants::STATUS_FORESEEN => _("#Foreseen"),
        C3op_Resources_ResponsibleStatusConstants::STATUS_CONTRACTED => _("#Contracted"),
        C3op_Resources_ResponsibleStatusConstants::STATUS_ACQUITTED => _("#Acquitted"),
        C3op_Resources_ResponsibleStatusConstants::STATUS_DISMISSED => _("#Dismissed"),
        C3op_Resources_ResponsibleStatusConstants::STATUS_CANCEL => _("#Canceled"),
        );
    }

    public function TitleForType($type)
    {
            switch ($type) {
                case C3op_Resources_ResponsibleStatusConstants::STATUS_NIL:
                case C3op_Resources_ResponsibleStatusConstants::STATUS_UNDEFINED:
                case C3op_Resources_ResponsibleStatusConstants::STATUS_FORESEEN:
                case C3op_Resources_ResponsibleStatusConstants::STATUS_CONTRACTED:
                case C3op_Resources_ResponsibleStatusConstants::STATUS_ACQUITTED:
                case C3op_Resources_ResponsibleStatusConstants::STATUS_DISMISSED:
                case C3op_Resources_ResponsibleStatusConstants::STATUS_CANCEL:
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