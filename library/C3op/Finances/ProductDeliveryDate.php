<?php

class C3op_Finances_ProductDeliveryDate {

    private $action;
    private $actionMapper;


    public function __construct(C3op_Projects_Action $action, C3op_Projects_ActionMapper $actionMapper)
    {
        $this->action = $action;
        $this->actionMapper = $actionMapper;

    }

    public function retrieve()
    {

        if ($this->action->getRequirementForReceiving()) {
            $receivableMapper = new C3op_Finances_ReceivableMapper();
            $receivable = $receivableMapper->findById($this->action->getRequirementForReceiving());
            return $receivable->GetDeliveryDate();
        } else {
            return null;
        }


    }


}


