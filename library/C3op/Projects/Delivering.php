<?php

class C3op_Projects_Delivering {

    private $delivery;
    private $deliveryMapper;

    public function __construct(C3op_Projects_Delivery $delivery, C3op_Projects_DeliveryMapper $deliveryMapper)
    {

        $this->delivery = $delivery;
        $this->deliveryMapper = $deliveryMapper;
    }

    public function wasDelivered()
    {
        if (is_null($this->delivery->GetRealDate())) {
            return false;
        } else {
            return true;
        }


    }
}
