<?php

class C3op_Resources_Responsible {

    protected $id;
    protected $action;
    protected $project;
    protected $description;
    protected $linkage;
    protected $value;
    protected $status;

    function __construct($id=0) {
        $this->id = (int)$id;
        $this->project = 0;
        $this->action = 0;
        $this->type = 0;
        $this->institution = 0;
        $this->contact = 0;
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
            throw new C3op_Resources_ResponsibleException('It\'s not possible to change a Responsible\'s ID');
        }
    } //setId

    public function getProject() {
        return $this->project;
    } //getId

    public function setProject($project) {
        if (($this->project == 0) && ($project > 0)) {
            $this->project = (int)$project;
        } else {
            throw new C3op_Resources_ResponsibleException('It\'s not possible to change a Responsible\'s project');
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
            throw new C3op_Resources_ResponsibleException("This ($action) is not a valid action.");
        }

    } //setAction

    public function getType() {
        return $this->type;
    } //getType

    public function setType($type) {
        $validator = new C3op_Util_ValidPositiveInteger();
        if ($validator->isValid($type)) {
            if ($this->type != $type) {
                $this->type = $type;
            }
        } else {
            throw new C3op_Resources_ResponsibleException("This ($type) is not a valid type.");
        }
    } //setType

    public function getInstitution() {
        return $this->institution;
    } //getInstitution

    public function setInstitution($institution) {
        $validator = new C3op_Util_ValidPositiveInteger();
        if ($validator->isValid($institution)) {
            if ($this->institution != $institution) {
                $this->institution = $institution;
                if ($institution > 0) {
                    $this->SetStatus(C3op_Resources_ResponsibleStatusConstants::STATUS_FORESEEN);
                    //$this->SetType(C3op_Resources_ResponsibleTypeConstants::TYPE_OUTSIDE_SERVICE);
                } else {
                    if (($this->GetStatus() != C3op_Resources_ResponsibleStatusConstants::STATUS_CANCEL)
                        && (!$this->getContact())){
                        $this->SetStatus(C3op_Resources_ResponsibleStatusConstants::STATUS_UNDEFINED);
                        //$this->SetType(C3op_Resources_ResponsibleTypeConstants::TYPE_NIL);
                    }
                }
            }
        } else {
            throw new C3op_Resources_ResponsibleException("This ($institution) is not a valid outside service.");
        }

    } //setInstitution

    public function getContact() {
        return $this->contact;
    } //getContact

    public function setContact($contact) {
        $validator = new C3op_Util_ValidPositiveInteger();
        if ($validator->isValid($contact)) {
            if ($this->contact != $contact) {
                $this->contact = $contact;
                if ($contact > 0) {
                    $this->SetStatus(C3op_Resources_ResponsibleStatusConstants::STATUS_FORESEEN);
                } else {
                    if (($this->GetStatus() != C3op_Resources_ResponsibleStatusConstants::STATUS_CANCEL)
                        && (!$this->getInstitution())){
                        $this->SetStatus(C3op_Resources_ResponsibleStatusConstants::STATUS_UNDEFINED);
                        //$this->SetType(C3op_Resources_ResponsibleTypeConstants::TYPE_NIL);
                    }
                }
            }
        } else {
            throw new C3op_Resources_ResponsibleException("This ($contact) is not a valid team member.");
        }

    } //setContact

    public function getValue() {
        return $this->value;
    } //getValue

    public function setValue($value) {
        $validator = new C3op_Util_ValidPositiveDecimal();
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
            throw new C3op_Resources_ResponsibleException("This ($status) is not a valid status.");
        }

    } //setStatus

}