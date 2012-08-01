<?php

class C3op_Register_Contact {
	
    protected $id;
    protected $name;
    protected $type;
	
    function __construct($id=0) {
        $this->id = (int)$id;
        $this->name = "";
    }

    public function GetId() {
        return $this->id;

    } //GetId

    public function SetId($id) {
        if (($this->id == 0) && ($id > 0)) {
            $this->id = (int)$id;
        } else {
            throw new C3op_Register_ContactException('It\'s not possible to change a contact\'s ID');
        }

    } //SetId

    public function GetName() {
        return $this->name;

    } //GetName
	
    public function SetName($name) {
        $validator = new C3op_Register_ContactValidName();
        if ($validator->isValid($name)) {
            if ($this->name != $name) {
                $this->name = $name;
            }
        } else {
            throw new C3op_Register_ContactException("This ($name) is not a valid name.");
        }

    } //SetName

    public function GetType() 
    {
        return $this->type;
        
    }
    
    public function SetType($type) 
    {
        switch ($type) {
            case C3op_Register_ContactConstants::CONTACT_GENERAL:
            case C3op_Register_ContactConstants::CONTACT_CLIENT:
            case C3op_Register_ContactConstants::CONTACT_PARTNER:
            case C3op_Register_ContactConstants::CONTACT_ASSOCIATE:
            case C3op_Register_ContactConstants::CONTACT_CONTRACTOR:
            case C3op_Register_ContactConstants::CONTACT_SUPPLIER:
                $this->type = (int) $type;
                break;

            default:
                throw new C3op_Register_ContactException("Invalid type.");
                break;
        }
    }

}