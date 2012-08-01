<?php

class C3op_Projects_HumanResource {
	
    protected $id;
    protected $action;
    protected $description;
    protected $contact;
    protected $value;
    protected $status;

    function __construct($id=0) {
        $this->id = (int)$id;
        $this->action = "";
        $this->description = "";
        $this->contact = "";
        $this->value = "";
        $this->status = "";
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

    public function GetContact() {
        return $this->contact;
    } //GetContact
	
    public function SetContact($contact) {
        $validator = new C3op_Util_ValidPositiveInteger();
        if ($validator->isValid($contact)) {
            if ($this->contact != $contact) {
                $this->contact = $contact;
                if ($contact > 0) {
                    $this->SetStatus(C3op_Projects_HumanResourceStatusConstants::STATUS_FORESEEN);
                } else {
                    if ($this->GetStatus() != C3op_Projects_HumanResourceStatusConstants::STATUS_CANCEL) {
                        $this->SetStatus(C3op_Projects_HumanResourceStatusConstants::STATUS_UNDEFINED);
                        
                    }
                }
            }
        } else {
            throw new C3op_Projects_HumanResourceException("This ($contact) is not a valid contact.");
        }

    } //SetContact

    public function GetValue() {
        return $this->value;
    } //GetValue
	
    public function SetValue($value) {
        $validator = new C3op_Util_ValidPositiveFloat();
        if ($validator->isValid($value)) {
            $this->value = $value;
        } else {
            $this->value = "";
        }
    } //SetValue
    
    public function GetStatus() {
        return $this->status;
    } //GetStatus
	
    public function SetStatus($status) {
        $validator = new C3op_Util_ValidPositiveInteger();
        if ($validator->isValid($status)) {
            if ($this->status != $status) {
                $this->status = $status;
            }
        } else {
            throw new C3op_Projects_HumanResourceException("This ($status) is not a valid status.");
        }

    } //SetStatus

    public function Realize() 
    {
        $this->SetDone(true);
        
    }
    
}