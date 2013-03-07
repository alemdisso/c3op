<?php

class C3op_Resources_MaterialSupply {

    protected $id;
    protected $action;
    protected $description;
    protected $institution;
    protected $linkage;
    protected $quantity;
    protected $unit;
    protected $unitValue;
    protected $totalValue;
    protected $status;

    function __construct($id=0) {
        $this->id = (int)$id;
        $this->action = "";
        $this->description = "";
        $this->institution = 0;
        $this->linkage = 0;
        $this->quantity = 0;
        $this->unit = "";
        $this->unitValue = 0;
        $this->totalValue = 0;
        $this->status = "";
    }

    public function getId() {
        return $this->id;
    } //getId

    public function setId($id) {
        if (($this->id == 0) && ($id > 0)) {
            $this->id = (int)$id;
        } else {
            throw new C3op_Resources_MaterialSupplyException('It\'s not possible to change a Human Resource\'s ID');
        }
    } //setId

    public function getAction() {
        return $this->action;
    } //getAction

    public function setAction($action) {
        $validator = new C3op_Util_ValidPositiveInteger();
        if ($validator->isValid($action)) {
            if ($this->action != $action) {
                $this->action = $action;
            }
        } else {
            throw new C3op_Resources_MaterialSupplyException("This ($action) is not a valid action.");
        }

    } //setAction

    public function getDescription() {
        return $this->description;
    } //getDescription

    public function setDescription($description) {
        $validator = new C3op_Util_ValidLongString();
        if ($validator->isValid($description)) {
            if ($this->description != $description) {
                $this->description = $description;
            }
        } else {
            throw new C3op_Resources_MaterialSupplyException("This ($description) is not a valid description.");
        }
    } //setDescription

    public function getInstitution() {
        return $this->institution;
    } //getInstitution

    public function setInstitution($institution) {
        $validator = new C3op_Util_ValidPositiveInteger();
        if ($validator->isValid($institution)) {
            if ($this->institution != $institution) {
                $this->institution = $institution;
                if ($institution > 0) {
                    $this->SetStatus(C3op_Resources_MaterialSupplyStatusConstants::STATUS_FORESEEN);
                } else {
                    if ($this->GetStatus() != C3op_Resources_MaterialSupplyStatusConstants::STATUS_CANCEL) {
                        $this->SetStatus(C3op_Resources_MaterialSupplyStatusConstants::STATUS_UNDEFINED);

                    }
                }
            }
        } else {
            throw new C3op_Resources_MaterialSupplyException("This ($institution) is not a valid institution.");
        }

    } //setInstitution

    public function getLinkage() {
        return $this->linkage;
    } //getLinkage

    public function setLinkage($linkage) {
        $validator = new C3op_Util_ValidPositiveInteger();
        if ($validator->isValid($linkage)) {
            if ($this->linkage != $linkage) {
                $this->linkage = $linkage;
            }
        } else {
            throw new C3op_Resources_MaterialSupplyException("This ($linkage) is not a valid linkage.");
        }

    } //setLinkage

    public function getQuantity() {
        return $this->quantity;
    } //getQuantity

    public function setQuantity($quantity) {
        $validator = new C3op_Util_ValidPositiveDecimal();
        if ($validator->isValid($quantity)) {
            $this->quantity = $quantity;
        } else {
            $this->quantity = "";
        }
    } //setQuantity

    public function getUnit() {
        return $this->unit;
    } //getUnit

    public function setUnit($unit) {
        $validator = new C3op_Util_ValidString();
        if ($validator->isValid($unit)) {
            if ($this->unit != $unit) {
                $this->unit = $unit;
            }
        } else {
            throw new C3op_Resources_MaterialSupplyException("This ($unit) is not a valid unit.");
        }
    } //setUnit

    public function getUnitValue() {
        return $this->unitValue;
    } //getUnitValue

    public function setUnitValue($unitValue) {
        $validator = new C3op_Util_ValidPositiveDecimal();
        if ($validator->isValid($unitValue)) {
            $this->unitValue = $unitValue;
        } else {
            $this->unitValue = "";
        }
    } //setUnitValue

    public function getTotalValue() {

        return $this->getUnitValue() * $this->GetQuantity();
    } //getTotalValue

    public function getStatus() {
        return $this->status;
    } //getStatus

    public function setStatus($status) {
        $validator = new C3op_Util_ValidPositiveInteger();
        if ($validator->isValid($status)) {
            if ($this->status != $status) {
                $this->status = $status;
            }
        } else {
            throw new C3op_Resources_MaterialSupplyException("This ($status) is not a valid status.");
        }

    } //setStatus

}