<?php

class C3op_Projects_ActionPhysicalProgress {

    private $action;
    private $actionMapper;

    public function __construct(C3op_Projects_Action $action, C3op_Projects_ActionMapper $actionMapper)
    {
        $this->action = $action;
        $this->actionMapper = $actionMapper;
    }

    public function physicalProgress(C3op_Projects_ActionsBelow $below)
    {
        if ($this->action->getBudgetForecast() > 0) {
            $totalDoneValue = $this->totalDoneValue($below);
            return $totalDoneValue / $this->action->getBudgetForecast();
        } else {
            return 0.0;
        }
    }

    public function totalDoneValue(C3op_Projects_ActionsBelow $below)
    {
        $allActionsInTree = $below->retrieve();
        $actionValueObj = new C3op_Projects_ActionCost($this->action,$this->actionMapper);

        $totalTreeDoneValue = $actionValueObj->individualDoneValue();

        foreach ($allActionsInTree as $actionId) {
            $loopAction = $this->actionMapper->findById($actionId);
            $loopActionValueObj = new C3op_Projects_ActionCost($loopAction,$this->actionMapper);
            $totalTreeDoneValue += $loopActionValueObj->individualDoneValue();
        }

        return $totalTreeDoneValue;
    }


}
