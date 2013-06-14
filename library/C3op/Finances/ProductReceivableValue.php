<?php

class C3op_Finances_ProductReceivableValue {

    private $action;
    private $actionMapper;


    public function __construct(C3op_Projects_Action $action, C3op_Projects_ActionMapper $actionMapper)
    {
        $this->action = $action;
        $this->actionMapper = $actionMapper;

    }

    public function retrieve(C3op_Finances_ReceivableMapper $mapper)
    {

        if ($this->action->getRequirementForReceiving()) {
            $receivable = $mapper->findById($this->action->getRequirementForReceiving());
            return $receivable->GetPredictedValue();
        } else {
            return null;
        }


    }


}


