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

    public function totalActionTreeCost(C3op_Projects_ActionsBelow $below, C3op_Resources_MaterialSupplyMapper $materialSupplierMapper, C3op_Resources_ResponsibleMapper $responsibleMapper)
    {
        $allActionsInTree = $below->retrieve();
        $actionValueObj = new C3op_Projects_ActionCost($this->action,$this->actionMapper);

        $totalTreeCost = $actionValueObj->individualCurrentValue();

        foreach ($allActionsInTree as $actionId) {
            $loopAction = $this->actionMapper->findById($actionId);
            $responsibleCost = $responsibleMapper->getResponsiblesValueJustForThisAction($loopAction);
            $materialCost = $materialSupplierMapper->getMaterialSuppliesValueJustForThisAction($loopAction);

            $totalTreeCost += $responsibleCost;
            $totalTreeCost += $materialCost;
        }

        return $totalTreeCost;
    }

    public function totalActionContractedValue(C3op_Projects_ActionsBelow $below, C3op_Resources_MaterialSupplyMapper $materialSupplierMapper)
    {
        $allActionsInTree = $below->retrieve();

        $totalTreeContractedValue = $this->individualContractedValue();

        foreach ($allActionsInTree as $actionId) {
            $loopAction = $this->actionMapper->findById($actionId);
            $actionValueObj = new C3op_Projects_ActionCost($loopAction,$this->actionMapper);
            $currentActionValue = $actionValueObj->individualContractedValue();
            $materialCost = $materialSupplierMapper->getContractedMaterialSuppliesValueJustForThisAction($loopAction);
            $totalTreeContractedValue += $currentActionValue;
            $totalTreeContractedValue += $materialCost;
        }

        return $totalTreeContractedValue;
    }

    public function individualBudgetValue()
    {
        return $this->action->getBudgetForecast();
    }

    public function individualContractedValue()
    {
        return $this->actionMapper->getContractedValueJustForThisAction($this->action)
                + $this->materialContractedValue();
    }

    public function individualPredictedValue()
    {
        return $this->actionMapper->getPredictedValueJustForThisAction($this->action)
                + $this->materialPredictedValue();
    }

    public function materialContractedValue()
    {
        return $this->actionMapper->getContractedMaterialSuppliesValueJustForThisAction($this->action);

    }

    public function materialPredictedValue()
    {
        return $this->actionMapper->getPredictedMaterialSuppliesValueJustForThisAction($this->action);

    }

    public function individualCurrentValue()
    {
        $contracted = $this->individualContractedValue();
        $predicted = $this->individualPredictedValue();

        return $predicted + $contracted;

    }

    public function individualDoneValue()
    {

        $tester = new C3op_Projects_ActionDone($this->action);
        if ($tester->isDone()) {
            echo "<br>########## DONE ###########<BR>";
            echo $this->action->getTitle();
            echo "<br>############################<br>";
            return $this->individualCurrentValue();
        } else {
            echo "<br>########## NOT DONE ###########<BR>";
            echo $this->action->getTitle();
            echo "<br>############################<br>";
            return 0;
        }

    }


}
