<?php

class C3op_Finances_ProductReceivableDate {

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
            if (!is_null($receivable->GetRealDate())) {
                return $receivable->GetRealDate();
            } else {
                return $receivable->GetPredictedDate();
            }
        } else {
            return null;
        }
    }


}


