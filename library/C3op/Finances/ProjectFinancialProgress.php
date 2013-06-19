<?php

class C3op_Finances_ProjectFinancialProgress {

    private $project;
    private $receivableMapper;

    public function __construct(C3op_Projects_Project $project, C3op_Finances_ReceivableMapper $receivableMapper)
    {
        $this->project = $project;
        $this->receivableMapper = $receivableMapper;

    }

    public function totalPredictedValueToReceive()
    {
        return $this->receivableMapper->getSumOfAllPredictedReceivables($this->project);
    }

    public function totalReceivedValue()
    {
        return $this->receivableMapper->getSumOfAllReceivedReceivables($this->project);
    }

    public function progress()
    {
         $predicted = $this->totalPredictedValueToReceive();

         if ($predicted > 0) {
            return sprintf("%.2f%%", 100 * $this->totalReceivedValue() / $predicted);
         }
    }

    public function totalPayedValue(C3op_Finances_OutlayMapper $mapper)
    {
        return $mapper->getSumOfAllPayedOutlays($this->project);
    }




}
