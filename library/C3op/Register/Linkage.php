<?php

class C3op_Register_Linkage
{

    protected $id;
    protected $contact;
    protected $institution;
    protected $department;
    protected $position;
    protected $phoneNumbers;
    protected $emails;

    function __construct($id=0) {
        $this->id = (int)$id;
        $this->contact = "";
        $this->institution = "";
        $this->department = "";
        $this->position = "";
        $this->phoneNumbers = array();
        $this->emails = array();
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


    public function GetPhoneNumbers() {
        return $this->phoneNumbers;

    } //GetPhoneNumbers

    public function SetPhoneNumbers($phoneNumbers) {
        $newArray = array();
        if (is_array($phoneNumbers)) {
            $validator = new C3op_Register_ValidPhoneLocalNumber();

            foreach ($phoneNumbers as $k => $phoneNumber) {
                if ($phoneNumber instanceOf C3op_Register_LinkagePhoneNumber) {
                    if ($validator->isValid($phoneNumber->GetLocalNumber())) {
                        $newArray[$k] = $phoneNumber;
                    } else {
                        throw new C3op_Register_LinkageException("A phone number must have at least a local number.");
                    }
                }
            }
        } else {
            throw new C3op_Register_LinkageException("Phone numbers must be organized in an array to be setted.");
        }
        $this->phoneNumbers = $newArray;
    } //SetPhoneNumbers

    public function AddPhoneNumber(C3op_Register_PhoneNumber $phoneNumber) {
        $validator = new C3op_Register_ValidPhoneLocalNumber();
        if ($validator->isValid($phoneNumber->GetLocalNumber())) {
            $this->phoneNumbers[] = $phoneNumber;
        } else {
            throw new C3op_Register_LinkageException("A phone number must have at least a local number.");
        }

    } //AddPhoneNumber

    public function RemovePhoneNumber($key) {

        if (is_array($this->phoneNumbers)) {
            if (!isset($this->phoneNumbers[$key])) {
                throw new C3op_Register_LinkageException("Phone number not found to be removed.");
            }
            unset($this->phoneNumbers[$key]);
        } else {
            throw new C3op_Register_LinkageException("There isn\'t phone numbers to remove");
        }

    } //SetPhoneNumbers

    public function GetEmails() {
        return $this->emails;

    } //GetEmails

    public function SetEmails($emails) {
        $newArray = array();
        if (is_array($emails)) {
            $validator = new C3op_Util_ValidEmail();

            foreach ($emails as $k => $email) {
                if ($email instanceOf C3op_Register_LinkageEmail) {
                    if ($validator->isValid($email->GetEmail())) {
                        $newArray[$k] = $email;
                    } else {
                        throw new C3op_Register_LinkageException("Invalid email.");
                    }
                }
            }
        } else {
            throw new C3op_Register_LinkageException("Emails must be organized in an array to be setted.");
        }
        $this->emails = $newArray;
    } //SetEmails

    public function AddEmail(C3op_Register_Email $email) {
        $validator = new C3op_Util_ValidEmail();
        if ($validator->isValid($email->GetEmail())) {
            $this->emails[] = $email;
        } else {
            throw new C3op_Register_LinkageException("Invalid email.");
        }

    } //AddEmail

    public function RemoveEmail(C3op_Register_Email $emailToBeRemoved) {

        if (is_array($this->emails)) {

            $found = false;
            foreach ($this->emails as $k => $eachEmail) {
                if ($emailToBeRemoved === $eachEmail) {
                    $found = true;
                    unset($this->emails[$k]);
                    return true;
                }
            }
            if (!$found) {
                throw new C3op_Register_LinkageException("Email not found to be removed.");
            }
        } else {
            throw new C3op_Register_LinkageException("There isn\'t emails to remove");
        }

    } //SetEmails


}