<?php

class C3op_Projects_ActionStatusTypes {

    private $status = array();


    public function __construct() {
        $this->status = array(
            C3op_Projects_ActionStatusConstants::STATUS_NIL => _("#Nil"),
            C3op_Projects_ActionStatusConstants::STATUS_PLAN => _("#Planning"),
            C3op_Projects_ActionStatusConstants::STATUS_IN_EXECUTION => _("#In Execution"),
            C3op_Projects_ActionStatusConstants::STATUS_RECEIVED => _("#Received"),
            C3op_Projects_ActionStatusConstants::STATUS_DONE => _("#Done"),
            C3op_Projects_ActionStatusConstants::STATUS_DELIVERED => _("#Delivered"),
            C3op_Projects_ActionStatusConstants::STATUS_COMPLETE => _("#Complete"),
            C3op_Projects_ActionStatusConstants::STATUS_ABORTED => _("#Aborted"),
        );
    }

    public function TitleForType($type)
    {
            switch ($type) {
                case C3op_Projects_ActionStatusConstants::STATUS_NIL:
                case C3op_Projects_ActionStatusConstants::STATUS_PLAN:
                case C3op_Projects_ActionStatusConstants::STATUS_IN_EXECUTION:
                case C3op_Projects_ActionStatusConstants::STATUS_RECEIVED:
                case C3op_Projects_ActionStatusConstants::STATUS_DONE:
                case C3op_Projects_ActionStatusConstants::STATUS_DELIVERED:
                case C3op_Projects_ActionStatusConstants::STATUS_COMPLETE:
                case C3op_Projects_ActionStatusConstants::STATUS_ABORTED:
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