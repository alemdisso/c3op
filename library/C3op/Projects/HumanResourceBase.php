<?php

class C3op_Projects_HumanResourceBase {
	
    protected $id;
    protected $action;
    protected $description;
    protected $value;

    function __construct($id=0) {
        $this->id = (int)$id;
        $this->action = "";
        $this->description = "";
        $this->value = "";
    }

    public function GetId() {
        return $this->id;
    } //GetId

    public function SetId($id) {
        if (($this->id == 0) && ($id > 0)) {
            $this->id = (int)$id;
        } else {
            throw new C3op_Projects_HumanResourceException('It\'s not possible to change a Human Resource\'s ID');
        }
    } //SetId

    public function GetAction() {
        return $this->action;
    } //GetAction
	
    public function SetAction($action) {
        $validator = new C3op_Util_ValidPositiveInteger();
        if ($validator->isValid($action)) {
            if ($this->action != $action) {
                $this->action = $action;
            }
        } else {
            throw new C3op_Projects_HumanResourceException("This ($action) is not a valid action.");
        }

    } //SetAction

    public function GetDescription() {
        return $this->description;
    } //GetDescription
	
    public function SetDescription($description) {
        $validator = new C3op_Util_ValidString();
        if ($validator->isValid($description)) {
            if ($this->description != $description) {
                $this->description = $description;
            }
        } else {
            throw new C3op_Projects_HumanResourceException("This ($description) is not a valid description.");
        }
    } //SetDescription

    public function GetValue() {
        return $this->value;
    } //GetValue
	
    public function SetValue($value) {
        if ($value) {
            $this->value = $value;
        } else {
            $this->value = "";
        }
    } //SetValue
    
}