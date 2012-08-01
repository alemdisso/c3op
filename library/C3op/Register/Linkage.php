<?php

class C3op_Register_Linkage
{
	
    protected $id;
    protected $contact;
    protected $institution;
    protected $department;
    protected $state;
    protected $position;

    function __construct($id=0) {
        $this->id = (int)$id;
        $this->contact = "";
        $this->institution = "";
        $this->department = "";
        $this->state = "";
        $this->position = "";
    }

    public function GetId() {
        return $this->id;
    } //GetId

    public function SetId($id) {
        if (($this->id == 0) && ($id > 0)) {
            $this->id = (int)$id;
        } else {
            throw new C3op_Register_LinkageException('It\'s not possible to change a Linkage\'s ID');
        }
    } //SetId

    public function GetContact() {
        return $this->contact;
    } //GetContact
	
    public function SetContact($contact) {
        $validator = new C3op_Util_ValidPositiveInteger();
        if ($validator->isValid($contact)) {
            if ($this->contact != $contact) {
                $this->contact = $contact;
            }
        } else {
            throw new C3op_Register_LinkageException("This ($contact) is not a valid contact.");
        }

    } //SetContact

    public function GetInstitution() {
        return $this->institution;
    } //GetInstitution
	
    public function SetInstitution($institution) {
        $validator = new C3op_Util_ValidPositiveInteger();
        if (($validator->isValid($institution)) && (strlen($institution) <= 40)) {
            if ($this->institution != $institution) {
                $this->institution = $institution;
            }
        } else {
            throw new C3op_Register_LinkageException("This ($institution) is not a valid institution.");
        }

    } //SetInstitution

    public function GetDepartment() {
        return $this->department;
    } //GetDepartment
	
    public function SetDepartment($department) {
        $validator = new C3op_Util_ValidString();
        if ($validator->isValid($department)) {
            if ($this->department != $department) {
                $this->department = $department;
            }
        } else {
            throw new C3op_Register_LinkageException("This ($department) is not a valid department.");
        }
    } //SetDepartment

    public function GetState() {
        return $this->state;
    } //GetState
	
    public function SetState($state) {
        if ($state) {
            $this->state = $state;
        } else {
            $this->state = "";
        }
    } //SetState

    public function GetPosition() {
        return $this->position;
    } //GetPosition
	
    public function SetPosition($position) {
        $validator = new C3op_Util_ValidString();
        if ($validator->isValid($position)) {
            if ($this->position != $position) {
                $this->position = $position;
            }
        } else {
            throw new C3op_Register_LinkageException("This ($position) is not a valid Local Register Number.");
        }

    } //SetPosition


}