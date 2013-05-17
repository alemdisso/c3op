<?php

class C3op_Projects_ActionDateChange {

    private $action;
    private $mapper;

    public function __construct(C3op_Projects_Action $action, C3op_Projects_ActionMapper $mapper)
    {
        $this->action = $action;
        $this->mapper = $mapper;
    }

    public function ChangePredictedBeginDate($newDate, $observation)
    {

        if ($this->action->GetPredictedBeginDate() != $newDate){

            $this->action->SetPredictedBeginDate($newDate);
            $this->mapper->update($this->action);

            $this->LogPredictedBeginDateChanging($observation);
        }

    }

    public function ChangeRealBeginDate($newDate, $observation)
    {

        if ($this->action->GetRealBeginDate() != $newDate){

            $this->action->SetRealBeginDate($newDate);
            $this->mapper->update($this->action);

            $this->LogRealBeginDateChanging($observation);
        }

    }

    public  function ChangePredictedFinishDate($newDate, $observation)
    {
        if ($this->action->GetPredictedFinishDate() != $newDate) {

            $this->action->SetPredictedFinishDate($newDate);
            $this->mapper->update($this->action);

            $this->LogPredictedFinishDateChanging($observation);
        }

    }

    private function LogPredictedBeginDateChanging($observation)
    {
        $this->LogADateChanging(C3op_Projects_ActionEventConstants::EVENT_PLANNED_BEGIN_DATE_CHANGE, $observation);
    }

    private function LogRealBeginDateChanging($observation)
    {
        $this->LogADateChanging(C3op_Projects_ActionEventConstants::EVENT_BEGIN_DATE_CHANGE, $observation);
    }

    private function LogPredictedFinishDateChanging($observation)
    {
        $this->LogADateChanging(C3op_Projects_ActionEventConstants::EVENT_PLANNED_FINISH_DATE_CHANGE, $observation);
    }

    private function LogADateChanging($event, $observation)
    {
        $logger = new C3op_Projects_EventLogger();
        $logger->LogActionEvent($this->action,
                $event,
                $observation);
    }




}


