<?php

class C3op_Projects_ActionCompletion {

    private $action;

    public function __construct(C3op_Projects_Action $action)
    {
        $this->action = $action;
    }

    public function daysDifference()
    {
        $tester = new C3op_Projects_ActionDone($this->action);
        $realFinishDate = $this->action->getRealFinishDate();
        $predictedFinishDate = $this->action->getPredictedFinishDate();

        $validator = new C3op_Util_ValidDate();
        $dateDiff = new C3op_Util_DatesDifferenceInDays();
        $now = time();
        if ($validator->isValid($predictedFinishDate)) {
            if ($tester->isDone()) {

                $differenceInDays = $dateDiff->differenceInDays(strtotime($predictedFinishDate), strtotime($realFinishDate));
            } else {
                $differenceInDays = $dateDiff->differenceInDays(strtotime($predictedFinishDate), $now);
            }

        } else {
            $differenceInDays = null;
        }
        return $differenceInDays;
    }

    public function finishDate()
    {
        $tester = new C3op_Projects_ActionDone($this->action);
        if ($tester->isDone()) {
            return $this->action->getRealFinishDate();
        } else {
            return $this->action->getPredictedFinishDate();
        }

    }



}
