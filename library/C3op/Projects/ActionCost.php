<?php

class C3op_Projects_ActionCost {

    private $action;
    private $actionMapper;

    public function __construct(C3op_Projects_Action $action, C3op_Projects_ActionMapper $actionMapper)
    {
        $this->action = $action;
        $this->actionMapper = $actionMapper;
    }

    public function totalValue()
    {
        return $this->actionMapper->getContractedValueForActionTree($this->action);
    }

    public function individualBudgetValue()
    {
        return $this->action->getBudgetForecast();
    }

    public function individualContractedValue()
    {
        return $this->actionMapper->getContractedValueJustForThisAction($this->action);

    }

    public function individualCurrentValue()
    {
        $contracted = $this->individualContractedValue();
        if ($contracted > 0) {
            return $contracted;
        } else {
            return $this->individualBudgetValue();
        }

    }


}
