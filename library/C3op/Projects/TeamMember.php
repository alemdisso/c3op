<?php

class C3op_Projects_TeamMember {

    protected $id;
    protected $action;
    protected $description;
    protected $linkage;
    protected $value;
    protected $status;

    function __construct($id=0) {
        $this->id = (int)$id;
        $this->action = "";
        $this->description = "";
        $this->linkage = 0;
        $this->value = "";
        $this->status = "";
    }

    public function getId() {
        return $this->id;
    } //getId

    public function setId($id) {
        if (($this->id == 0) && ($id > 0)) {
            $this->id = (int)$id;
        } else {
            throw new C3op_Projects_TeamMemberException('It\'s not possible to change a Human Resource\'s ID');
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
            throw new C3op_Projects_TeamMemberException("This ($action) is not a valid action.");
        }

    } //setAction

    public function getDescription() {
        return $this->description;
    } //getDescription

    public function setDescription($description) {
        $validator = new C3op_Util_ValidString();
        if ($validator->isValid($description)) {
            if ($this->description != $description) {
                $this->description = $description;
            }
        } else {
            throw new C3op_Projects_TeamMemberException("This ($description) is not a valid description.");
        }
    } //setDescription

    public function getLinkage() {
        return $this->linkage;
    } //getLinkage

    public function setLinkage($linkage) {
        $validator = new C3op_Util_ValidPositiveInteger();
        if ($validator->isValid($linkage)) {
            if ($this->linkage != $linkage) {
                $this->linkage = $linkage;
                if ($linkage > 0) {
                    $this->SetStatus(C3op_Projects_TeamMemberStatusConstants::STATUS_FORESEEN);
                } else {
                    if ($this->GetStatus() != C3op_Projects_TeamMemberStatusConstants::STATUS_CANCEL) {
                        $this->SetStatus(C3op_Projects_TeamMemberStatusConstants::STATUS_UNDEFINED);

                    }
                }
            }
        } else {
            throw new C3op_Projects_TeamMemberException("This ($linkage) is not a valid linkage.");
        }

    } //setLinkage

    public function setRawLinkage($linkage) {
        $validator = new C3op_Util_ValidPositiveInteger();
        if ($validator->isValid($linkage)) {
            if ($this->linkage != $linkage) {
                $this->linkage = $linkage;
            }
        } else {
            throw new C3op_Projects_TeamMemberException("This ($linkage) is not a valid linkage.");
        }

    } //setLinkage

    public function getValue() {
        return $this->value;
    } //getValue

    public function setValue($value) {
        $validator = new C3op_Util_ValidPositiveFloat();
        if ($validator->isValid($value)) {
            $this->value = $value;
        } else {
            $this->value = "";
        }
    } //setValue

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
            throw new C3op_Projects_TeamMemberException("This ($status) is not a valid status.");
        }

    } //setStatus

}