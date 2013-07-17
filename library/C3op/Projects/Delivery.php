<?php

class C3op_Projects_Delivery {

    protected $id;
    protected $receivable;
    protected $project;
    protected $predictedDate;
    protected $realDate;
    protected $receivablePredictedValue;
    protected $receivablePredictedDate;

    function __construct($receivable, $project, $predictedDate, $id=0) {
        $this->id = (int)$id;
        $this->receivable = $receivable;
        $this->project = $project;
        $this->predictedDate = $predictedDate;
        $this->receivablePredictedDate = null;
    }

    public function GetId() {
        return $this->id;

    } //GetId

    public function SetId($id) {
        if (($this->id == 0) && ($id > 0)) {
            $this->id = (int)$id;
        } else {
            throw new C3op_Projects_DeliveryException('It\'s not possible to change a delivery\'s ID');
        }
    } //SetId

    public function GetReceivable()
    {
        return $this->receivable;
    }

    public function SetReceivable($receivable)
    {
        if ($this->receivable != $receivable) {
            $validator = new C3op_Util_ValidPositiveInteger();
            if ($validator->isValid($receivable)) {
                $this->receivable = $receivable;
            }
        }
    }

    public function GetReceivablePredictedValue()
    {
        return $this->receivablePredictedValue;

    } //GetPredictedDate

    public function GetReceivablePredictedDate()
    {
        return $this->receivablePredictedDate;

    } //GetPredictedDate



    public function GetProject()
    {
        return $this->project;
    }

    public function SetProject($project)
    {
        if ($this->project != $project) {
            $validator = new C3op_Util_ValidPositiveInteger();
            if ($validator->isValid($project)) {
                $this->project = $project;
            }
        }
    }

    public function GetPredictedDate()
    {
        return $this->predictedDate;

    } //GetPredictedDate

    public function SetPredictedDate($predictedDate)
    {

        $dateValidator = new C3op_Util_ValidDate();
        if ($dateValidator->isValid($predictedDate)) {
            if ($this->predictedDate != $predictedDate) {
                $this->predictedDate = $predictedDate;
            }
        } else {
            throw new C3op_Projects_DeliveryException("This ($predictedDate) is not a valid date of begin.");
        }
    } //SetPredictedDate

    public function GetRealDate()
    {
        return $this->realDate;

    } //GetRealDate

    public function SetRealDate($realDate)
    {
        if ($realDate != "") {
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($realDate)) {
                if ($this->realDate != $realDate) {
                    $this->realDate = $realDate;
                }
            } else {
                throw new C3op_Projects_DeliveryException("This ($realDate) is not a valid date of begin.");
            }
        }
    } //SetRealDate

}