<?php

class C3op_Projects_ProjectBase {
	
    protected $id;
    protected $title;
    protected $client;
    protected $ourResponsible;
    protected $responsibleAtClient;
    protected $dateBegin;
    protected $dateFinish;
    protected $value;
    protected $natureOfContract;
    protected $areaActivity;
    protected $overhead;
    protected $managementFee;
    protected $object;
    protected $summary;
    protected $observation;
    
	
    function __construct($id=0) {
        $this->id = (int)$id;
        $this->title = "";
        $this->dateBegin = "";
        $this->value = 0;
    }

    public function GetId()
    {
        return $this->id;
    } //GetId

    public function SetId($id)
    {
        if (($this->id == 0) && ($id > 0)) {
            $this->id = (int)$id;
        } else {
            throw new C3op_Projects_ProjectException('It\'s not possible to change a project\'s ID');
        }
    } //SetId

    public function GetTitle()
    {
        return $this->title;
    } //GetTitle
	
    public function SetTitle($title)
    {
        $validator = new C3op_Projects_ProjectValidTitle();
        if ($validator->isValid($title)) {
            if ($this->title != $title) {
                $this->title = $title;
            }
        } else {
            throw new C3op_Projects_ProjectException("This ($title) is not a valid title.");
        }
    } //SetTitle

    public function GetDateBegin()
    {
        return $this->dateBegin;
    } //GetDateBegin
	
    public function SetDateBegin($dateBegin)
    {
        if ($dateBegin != "") {
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($dateBegin)) {
                if ($this->dateBegin != $dateBegin) {
                    $this->dateBegin = $dateBegin;
                }
            } else {
                throw new C3op_Projects_ProjectException("This ($dateBegin) is not a valid date of begin.");
            }
        }
    } //SetDateBegin

    public function SetValue($value) 
    {
        $validator = new C3op_Util_ValidPositiveFloat();
        
        if ($validator->isValid($value)) {
            $this->value = (float) $value;
        } else {
            throw new C3op_Projects_ProjectException("Value must be a positive number.");
        }
    }

    public function GetValue() 
    {
        return $this->value;
    }
}