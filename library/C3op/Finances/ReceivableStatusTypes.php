<?php

class C3op_Finances_ReceivableStatusTypes {

    private $status = array();


    public function __construct() {
        $this->status = array(
            C3op_Finances_ReceivableStatusConstants::STATUS_NIL           => _("#Nil"),
            C3op_Finances_ReceivableStatusConstants::STATUS_NOT_DELIVERED => _("#Not Delivered"),
            C3op_Finances_ReceivableStatusConstants::STATUS_PARTIAL       => _("#Partial"),
            C3op_Finances_ReceivableStatusConstants::STATUS_DELIVERED     => _("#Delivered"),
            C3op_Finances_ReceivableStatusConstants::STATUS_RECEIVED      => _("#(ReceivableStatusType)Received"),
        );
    }

    public function TitleForType($type)
    {
            switch ($type) {
                case C3op_Finances_ReceivableStatusConstants::STATUS_NIL:
                case C3op_Finances_ReceivableStatusConstants::STATUS_NOT_DELIVERED:
                case C3op_Finances_ReceivableStatusConstants::STATUS_PARTIAL:
                case C3op_Finances_ReceivableStatusConstants::STATUS_DELIVERED:
                case C3op_Finances_ReceivableStatusConstants::STATUS_RECEIVED:
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