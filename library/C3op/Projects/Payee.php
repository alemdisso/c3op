<?php

class C3op_Projects_Payee
{

    protected $id;
    protected $project;
    protected $action;
    protected $humanResource;
    protected $outsideService;
    protected $materialSupplier;

    function __construct($humanResource=0, $outsideService=0, $materialSupplier=0, $id=0) {
        $this->id = (int)$id;

        if (($humanResource) || ($outsideService) || ($materialSupplier)) {
            $this->humanResource = $humanResource;
            $this->outsideService = $outsideService;
            $this->materialSupplier = $materialSupplier;
        } else {
            throw new C3op_Projects_PayleeException('Can\'t create a payee without someone to pay.');
        }
    }


    public function GetId() {
        return $this->id;

    } //GetId

    public function SetId($id) {
        if (($this->id == 0) && ($id > 0)) {
            $this->id = (int)$id;
        } else {
            throw new C3op_Projects_OutlayException('It\'s not possible to change a outlay\'s ID');
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

    public function GetAction()
    {
        return $this->action;
    }

    public function SetAction($action)
    {
        $this->action = $action;
    }

    public function GetTeamMember()
    {
        return $this->humanResource;
    }

    public function SetTeamMember($humanResource)
    {
        $this->humanResource = $humanResource;
    }

    public function GetPredictedValue()
    {
        return $this->predictedValue;

    }

    public function SetPredictedValue($predictedValue)
    {
        if ($predictedValue >= 0) {
            $this->predictedValue = (float) $predictedValue;
        } else {
            throw new C3op_Projects_OutlayException("Value must be a positive number.");

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
            throw new C3op_Projects_OutlayException("This ($predictedDate) is not a valid date of begin.");
        }
    } //SetPredictedDate

    public function GetRealValue()
    {
        return $this->realValue;

    }

    public function SetRealValue($realValue)
    {
        if ($realValue >= 0) {
            $this->realValue = (float) $realValue;
        } else {
            throw new C3op_Projects_OutlayException("Value must be a positive number.");

        }
    }

    public function GetRealDate()
    {
        return $this->realDate;

    } //GetRealDate

    public function SetRealDate($realDate)
    {

        $dateValidator = new C3op_Util_ValidDate();
        if ($dateValidator->isValid($realDate)) {
            if ($this->realDate != $realDate) {
                $this->realDate = $realDate;
            }
        } else {
            throw new C3op_Projects_OutlayException("This ($realDate) is not a valid date of begin.");
        }
    } //SetRealDate

     public function GetRecurrent()
    {
        return $this->recurrent;
    }

    public function SetRecurrent($recurrent)
    {
        $this->recurrent = $recurrent;
    }

  public function GetObservation()
    {
        return $this->observation;
    } //GetObservation

    public function SetObservation($observation)
    {
        $validator = new C3op_Util_ValidLongString();
        if ($validator->isValid($observation)) {
            if ($this->observation != $observation) {
                $this->observation = $observation;
            }
        } else {
            throw new C3op_Projects_OutlayException("This ($observation) is not a valid observation.");
        }
    } //SetObservation





}