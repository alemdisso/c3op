<?php

class C3op_Projects_DeliveryMade {

    private $receivable;
    private $deliveryMapper;


    public function __construct(C3op_Finances_Receivable $receivable, C3op_Projects_DeliveryMapper $deliveryMapper)
    {
        $this->receivable = $receivable;
        $this->deliveryMapper = $deliveryMapper;
    }

    public function wasDelivered()
    {
        try {
            $delivery = $this->deliveryMapper->findByReceivableId($this->receivable->getId());
        } catch (Exception $e) {
            return false;
        }

        $realDate = $delivery->GetRealDate();
        if (!is_null($realDate)) {
            return true;
        } else {
            return false;
        }
    }
}
