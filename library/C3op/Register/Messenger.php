<?php

class C3op_Register_Messenger {

    protected $id;
    protected $address;
    protected $service;

    function __construct($id=0, $address="", $service="") {
        $this->id = (int)$id;
        $this->address = $address;
        $this->service = $service;
    }

    public function GetId() {
        return $this->id;
    } //GetId

    public function SetId($id) {
        if (($this->id == 0) && ($id > 0)) {
            $this->id = (int)$id;
        } else {
            throw new C3op_Register_MessengerException('It\'s not possible to change a messenger\'s ID');
        }
    } //SetId

    public function GetAddress() {
        return $this->address;
    } //GetAddress

    public function SetAddress($address) {
        $validator = new C3op_Util_ValidString();
        if ($validator->isValid($address)) {
            if ($this->address != $address) {
                $this->address = $address;
            }
        } else {
            throw new C3op_Register_ContactException("This ($address) is not a valid one.");
        }
    } //SetAddress

    public function GetService() {
        return $this->service;
    } //GetService

    public function SetService($service) {
        $validator = new C3op_Util_ValidString();
        if (($service == "") || ($validator->isValid($service))) {
            if ($this->service != $service) {
                $this->service = $service;
            }
        } else {
            throw new C3op_Register_ContactException("This ($service) is not a valid service.");
        }
    } //SetService

}