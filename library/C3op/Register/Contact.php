<?php

class C3op_Register_Contact {

    protected $id;
    protected $name;
    protected $type;
    protected $phoneNumbers;
    protected $emails;
    protected $messengers;

    function __construct($id=0) {
        $this->id = (int)$id;
        $this->name = "";
        $this->phoneNumbers = array();
        $this->emails = array();
        $this->messengers = array();
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

    public function GetPhoneNumbers() {
        return $this->phoneNumbers;

    } //GetPhoneNumbers

    public function SetPhoneNumbers($phoneNumbers) {
        $newArray = array();
        if (is_array($phoneNumbers)) {
            $validator = new C3op_Register_ValidPhoneLocalNumber();
            foreach ($phoneNumbers as $k => $phoneNumber) {
                if ($phoneNumber instanceOf C3op_Register_ContactPhoneNumber) {
                    if ($validator->isValid($phoneNumber->GetLocalNumber())) {
                        $newArray[$k] = $phoneNumber;
                    } else {
                        throw new C3op_Register_ContactException("A phone number must have at least a local number.");
                    }
                }
            }
        } else {
            throw new C3op_Register_ContactException("Phone numbers must be organized in an array to be setted.");
        }
        $this->phoneNumbers = $newArray;
    } //SetPhoneNumbers

    public function AddPhoneNumber(C3op_Register_PhoneNumber $phoneNumber) {
        $validator = new C3op_Register_ValidPhoneLocalNumber();
        if ($validator->isValid($phoneNumber->GetLocalNumber())) {
            $this->phoneNumbers[] = $phoneNumber;
        } else {
            throw new C3op_Register_ContactException("A phone number must have at least a local number.");
        }

    } //AddPhoneNumber

    public function RemovePhoneNumber(C3op_Register_PhoneNumber $phoneNumberToBeRemoved) {

        if (is_array($this->phoneNumbers)) {

            $found = false;
            foreach ($this->phoneNumbers as $k => $eachPhoneNumber) {
                if ($phoneNumberToBeRemoved === $eachPhoneNumber) {
                    $found = true;
                    unset($this->phoneNumbers[$k]);
                    return true;
                }
            }
            if (!$found) {
                throw new C3op_Register_ContactException("Phone number not found to be removed.");
            }
        } else {
            throw new C3op_Register_ContactException("There isn\'t phone numbers to remove");
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
                if ($email instanceOf C3op_Register_ContactEmail) {
                    if ($validator->isValid($email->GetEmail())) {
                        $newArray[$k] = $email;
                    } else {
                        throw new C3op_Register_ContactException("Invalid email.");
                    }
                }
            }
        } else {
            throw new C3op_Register_ContactException("Emails must be organized in an array to be setted.");
        }
        $this->emails = $newArray;
    } //SetEmails

    public function AddEmail(C3op_Register_Email $email) {
        $validator = new C3op_Util_ValidEmail();
        if ($validator->isValid($email->GetEmail())) {
            $this->emails[] = $email;
        } else {
            throw new C3op_Register_ContactException("Invalid email.");
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
                throw new C3op_Register_ContactException("Email not found to be removed.");
            }
        } else {
            throw new C3op_Register_ContactException("There isn\'t emails to remove");
        }

    } //RemoveEmail

    public function GetMessengers() {
        return $this->messengers;

    } //GetMessengers

    public function SetMessengers($messengers) {
        $newArray = array();
        if (is_array($messengers)) {
            $validator = new C3op_Util_ValidMessenger();

            foreach ($messengers as $k => $messenger) {
                if ($messenger instanceOf C3op_Register_ContactMessenger) {
                    if ($validator->isValid($messenger->GetMessenger())) {
                        $newArray[$k] = $messenger;
                    } else {
                        throw new C3op_Register_ContactException("Invalid messenger.");
                    }
                }
            }
        } else {
            throw new C3op_Register_ContactException("Messengers must be organized in an array to be setted.");
        }
        $this->messengers = $newArray;
    } //SetMessengers

    public function AddMessenger(C3op_Register_Messenger $messenger) {
        $validator = new C3op_Util_ValidMessenger();
        if ($validator->isValid($messenger->GetMessenger())) {
            $this->messengers[] = $messenger;
        } else {
            throw new C3op_Register_ContactException("Invalid messenger.");
        }

    } //AddMessenger

    public function RemoveMessenger(C3op_Register_Messenger $messengerToBeRemoved) {

        if (is_array($this->messengers)) {

            $found = false;
            foreach ($this->messengers as $k => $eachMessenger) {
                if ($messengerToBeRemoved === $eachMessenger) {
                    $found = true;
                    unset($this->messengers[$k]);
                    return true;
                }
            }
            if (!$found) {
                throw new C3op_Register_ContactException("Messenger not found to be removed.");
            }
        } else {
            throw new C3op_Register_ContactException("There isn\'t messengers to remove");
        }

    } //RemoveMessenger

}