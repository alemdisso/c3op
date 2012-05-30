<?php

class C3op_Projects_ReceivingBase {
	
    protected $id;
    protected $name;
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
            throw new C3op_Projects_ReceivingException('It\'s not possible to change a receiving\'s ID');
        }
    } //SetId

    public function GetProject()
    {
        return $this->project;
    }
	
    public function SetProject($project) 
    {
        $this->project = $project;
    }
    
    public function GetName() 
    {
        return $this->name;
    } //GetName
	
    public function SetName($name)
    {
        //$validator = new Zend_Validate_Regex("/^[0-9a-zA-ZÀ-ú]+[0-9A-Za-zÀ-ú\'\[\]\(\)\-\.\,\:\;\!\? ]{1,50}$/");
        $validator = new C3op_Projects_ReceivingValidTitle();
        if ($validator->isValid($name)) {
            if ($this->name != $name) {
                $this->name = $name;
            }
        } else {
            throw new C3op_Projects_ReceivingException("This ($name) is not a valid name.");
        }

    } //SetName

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
            throw new C3op_Projects_ReceivingException("This ($predictedDate) is not a valid date of begin.");
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
                throw new C3op_Projects_ReceivingException("This ($realDate) is not a valid date of begin.");
            }
        }
    } //SetRealDate

    public function SetPredictedValue($predictedValue) 
    {
        if ($predictedValue >= 0) {
            $this->predictedValue = (float) $predictedValue;
        } else {
            throw new C3op_Projects_ReceivingException("Value must be a positive number.");
            
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
            throw new C3op_Projects_ReceivingException("Real value of a receiving must be a positive number.");
        }
    }

    public function GetRealValue() 
    {
        return $this->realValue;
        
    }    
}