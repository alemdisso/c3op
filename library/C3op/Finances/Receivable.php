<?php

class C3op_Finances_Receivable {

    protected $id;
    protected $project;
    protected $title;
    protected $description;
    protected $deliveryDate;
    protected $predictedDate;
    protected $predictedValue;
    protected $realDate;
    protected $realValue;

    function __construct($project, $predictedDate, $predictedValue, $id=0) {
        $this->id = (int)$id;
        $this->project = $project;
        $this->predictedDate = $predictedDate;
        $this->predictedValue = $predictedValue;
    }

    public function GetId() {
        return $this->id;

    } //GetId

    public function SetId($id) {
        if (($this->id == 0) && ($id > 0)) {
            $this->id = (int)$id;
        } else {
            throw new C3op_Finances_ReceivableException('It\'s not possible to change a receivable\'s ID');
        }
    } //SetId

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

    public function GetTitle()
    {
        return $this->title;
    } //GetTitle

    public function SetTitle($title)
    {
        $validator = new C3op_Util_ValidString();
        if ($validator->isValid($title)) {
            if ($this->title != $title) {
                $this->title = $title;
            }
        } else {
            throw new C3op_Finances_ReceivableException("This ($title) is not a valid title.");
        }

    } //SetTitle

    public function GetDescription()
    {
        return $this->description;
    } //GetDescription

    public function SetDescription($description)
    {
        $validator = new C3op_Util_ValidLongString();
        if ($validator->isValid($description)) {
            if ($this->description != $description) {
                $this->description = $description;
            }
        } else {
            throw new C3op_Finances_ReceivableException("This ($description) is not a valid description.");
        }

    } //SetDescription

    public function GetDeliveryDate()
    {
        return $this->deliveryDate;

    } //GetDeliveryDate

    public function GetPredictedDate()
    {
        return $this->predictedDate;

    } //GetPredictedDate

    public function SetPredictedDate($predictedDate)
    {

        $dateValidator = new C3op_Util_ValidDate();
        if (($dateValidator->isValid($predictedDate)) || (is_null($predictedDate))) {
            if ($this->predictedDate != $predictedDate) {
                $this->predictedDate = $predictedDate;
            }
        } else {
            throw new C3op_Finances_ReceivableException("This ($predictedDate) is not a valid date of begin.");
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
                throw new C3op_Finances_ReceivableException("This ($realDate) is not a valid date of begin.");
            }
        }
    } //SetRealDate

    public function SetPredictedValue($predictedValue)
    {
        if ($predictedValue >= 0) {
            $this->predictedValue = (float) $predictedValue;
        } else {
            throw new C3op_Finances_ReceivableException("Value must be a positive number.");

        }
    }

    public function GetPredictedValue()
    {
        return $this->predictedValue;

    }

    public function SetRealValue($realValue)
    {
        if ($realValue >= 0) {
            $this->realValue = (float) $realValue;
        } else {
            throw new C3op_Finances_ReceivableException("Real value of a receivable must be a positive number.");
        }
    }

    public function GetRealValue()
    {
        return $this->realValue;

    }
}