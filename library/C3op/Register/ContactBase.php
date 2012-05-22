<?php

class C3op_Projects_ProjectBase {
	
    protected $id;
    protected $title;
    protected $dateBegin;
    protected $value;
	
    function __construct($id=0) {
        $this->id = $id;
        $this->title = "";
        $this->dateBegin = "";
        $this->value = 0;
    }

    public function GetId() {
        return $this->id;

    } //GetId

    public function SetId($id) {
        if (($this->id == 0) && ($id > 0)) {
            $this->id = $id;
        } else {
            throw new C3op_Projects_ProjectException('It\'s not possible to change a project\'s ID');
        }

    } //SetId

    public function GetTitle() {
        return $this->title;

    } //GetTitle
	
    public function SetTitle($title) {
        //$validator = new Zend_Validate_Regex("/^[0-9a-zA-ZÀ-ú]+[0-9A-Za-zÀ-ú\'\[\]\(\)\-\.\,\:\;\!\? ]{1,50}$/");
        $validator = new C3op_Projects_Util_ValidTitle();
        if ($validator->isValid($title)) {
            if ($this->title != $title) {
                $this->title = $title;
            }
        } else {
            throw new C3op_Projects_ProjectException("This ($title) is not a valid title.");
        }

    } //SetTitle

    public function GetDateBegin() {
        return $this->dateBegin;

    } //GetDateBegin
	
    public function SetDateBegin($dateBegin) {
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
        if ($value >= 0) {
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