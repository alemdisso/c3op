<?php

class C3op_Finances_ActionBalance {

    private $action;
    private $actionMapper;

    public function __construct(C3op_Projects_Action $action, C3op_Projects_ActionMapper $actionMapper)
    {
        $this->action = $action;
        $this->actionMapper = $actionMapper;
    }

    public function getBalance() {
        $budgetForecast = $this->action->getBudgetForecast();
        return $budgetForecast - $this->getTotalCurrentCost();

    }

    public function getTotalCurrentCost() {
        $actionValueObj = new C3op_Projects_ActionCost($this->action,$this->actionMapper);
        $actionsBelow = new C3op_Projects_ActionsBelow($this->action,$this->actionMapper);
        $totalCurrentCost = $actionValueObj->totalActionTreeCost($actionsBelow
                                                            , new C3op_Resources_MaterialSupplyMapper
                                                            , new C3op_Resources_ResponsibleMapper
                                                        );

        return $totalCurrentCost;

    }


}
