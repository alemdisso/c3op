<?php

class C3op_Projects_Action {

    protected $id;
    protected $title = "";
    protected $project;
    protected $done=false;
    protected $status;
    protected $description;
    protected $begin;
    protected $subordinatedTo = 0;
    protected $responsible = 0;
    protected $milestone = false;
    protected $requirementForReceiving = false;
    protected $predictedBeginDate = "0000-00-00";
    protected $predictedFinishDate = "0000-00-00";
    protected $realBeginDate = "0000-00-00";
    protected $realFinishDate = "0000-00-00";
    protected $receiptDate = null;

    function __construct($project, $id=0)
    {
        $this->project = $project;
        $this->id = (int)$id;

    }

    public function getId()
    {
        return $this->id;

    } //GetId

    public function setId($id)
    {
        if (($this->id == 0) && ($id > 0)) {
            $this->id = (int)$id;
        } else {
            throw new C3op_Projects_ActionException('It\'s not possible to change a action\'s ID');
        }

    } //SetId

    public function getTitle()
    {
        return $this->title;

    } //GetTitle

    public function setTitle($title)
    {
        $validator = new C3op_Projects_ProjectValidTitle();
        if ($validator->isValid($title)) {
            if ($this->title != $title) {
                $this->title = $title;
            }
        } else {
            throw new C3op_Projects_ActionException("This ($title) is not a valid title.");
        }

    } //SetTitle

    public function getProject()
    {
        return $this->project;
    }

    public function setProject($project)
    {
        $this->project = $project;
    }

    public function setDone($done)
    {
        if ($done) {
            $this->done = $done;
        } else {
            $this->done = 0;
        }
    }

    public function getDone()
    {
        return $this->done;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {

        switch ($status) {
            case C3op_Projects_ActionStatusConstants::STATUS_NIL:
            case C3op_Projects_ActionStatusConstants::STATUS_PLAN:
            case C3op_Projects_ActionStatusConstants::STATUS_IN_EXECUTION:
            case C3op_Projects_ActionStatusConstants::STATUS_RECEIVED:
            case C3op_Projects_ActionStatusConstants::STATUS_DONE:
            case C3op_Projects_ActionStatusConstants::STATUS_DELIVERED:
            case C3op_Projects_ActionStatusConstants::STATUS_COMPLETE:
            case C3op_Projects_ActionStatusConstants::STATUS_ABORT:
                $this->status = (int)$status;
                break;

            case null:
            case "":
            case 0:
            case false:
                $this->type = null;
                break;

            default:
                throw new C3op_Projects_ActionException("Invalid action status.");
                break;
        }
    }

    public function getDescription()
    {
        return $this->description;
    } //GetDescription

    public function setDescription($description)
    {
        $validator = new C3op_Util_ValidLongString();
        if ($validator->isValid($description)) {
            if ($this->description != $description) {
                $this->description = $description;
            }
        } else {
            throw new C3op_Projects_ActionException("This ($description) is not a valid description.");
        }
    } //SetDescription

    public function getBegin()
    {
        return $this->begin;
    } //GetBegin

    public function setBegin($begin)
    {
        if ($begin != "") {
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($begin)) {
                if ($this->begin != $begin) {
                    $this->begin = $begin;
                }
            } else {
                throw new C3op_Projects_ActionException("This ($begin) is not a valid date of begin.");
            }
        }
    } //SetBegin

    public function setSubordinatedTo($subordinatedTo)
    {
        $this->subordinatedTo = $subordinatedTo;
    }

    public function getSubordinatedTo()
    {
        return $this->subordinatedTo;
    }


    public function getResponsible()
    {
        return $this->responsible;
    }

    public function setResponsible($responsible)
    {
        if ($this->responsible != $responsible) {
            $validator = new C3op_Util_ValidPositiveInteger();
            if ($validator->isValid($responsible)) {
                $this->responsible = $responsible;
            }
        }
    }

    public function setMilestone($milestone)
    {
        if ($milestone) {
            $this->milestone = $milestone;
        } else {
            $this->milestone = 0;
        }
    }

    public function getMilestone()
    {
        return $this->milestone;
    }

    public function setRequirementForReceiving($requirementForReceiving)
    {
        if ($requirementForReceiving) {
            $this->requirementForReceiving = $requirementForReceiving;
        } else {
            $this->requirementForReceiving = 0;
        }
    }

    public function getRequirementForReceiving()
    {
        return $this->requirementForReceiving;
    }

    public function getPredictedBeginDate()
    {
        return $this->predictedBeginDate;
    } //GetPredictedBeginDate

    public function setPredictedBeginDate($predictedBeginDate)
    {
        if ($predictedBeginDate != "") {
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($predictedBeginDate)) {
                if ($this->predictedBeginDate != $predictedBeginDate) {
                    $this->predictedBeginDate = $predictedBeginDate;
                }
            } else {
                throw new C3op_Projects_ActionException("This ($predictedBeginDate) is not a valid date of begin.");
            }
        }
    } //SetPredictedBeginDate

    public function getPredictedFinishDate()
    {
        return $this->predictedFinishDate;
    } //GetPredictedFinishDate

    public function setPredictedFinishDate($predictedFinishDate)
    {
        if ($predictedFinishDate != "") {
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($predictedFinishDate)) {
                if ($this->predictedFinishDate != $predictedFinishDate) {
                    $this->predictedFinishDate = $predictedFinishDate;
                }
            } else {
                throw new C3op_Projects_ActionException("This ($predictedFinishDate) is not a valid date of finish.");
            }
        }
    } //SetPredictedFinishDate

    public function getRealBeginDate()
    {
        return $this->realBeginDate;
    } //GetRealBeginDate

    public function setRealBeginDate($realBeginDate)
    {
        if ($realBeginDate != "") {
            $dateValidator = new C3op_Util_ValidDate();
            if (($realBeginDate == "0000-00-00") || ($dateValidator->isValid($realBeginDate))) {
                if ($this->realBeginDate != $realBeginDate) {
                    $this->realBeginDate = $realBeginDate;
                }
            } else {
                throw new C3op_Projects_ActionException("This ($realBeginDate) is not a valid date of begin.");
            }
        }
    } //SetRealBeginDate

    public function getRealFinishDate()
    {
        return $this->realFinishDate;
    } //GetRealFinishDate

    public function setRealFinishDate($realFinishDate)
    {
        if ($realFinishDate != "") {
            $dateValidator = new C3op_Util_ValidDate();
            if (($realFinishDate == "0000-00-00") || ($dateValidator->isValid($realFinishDate))) {
                if ($this->realFinishDate != $realFinishDate) {
                    $this->realFinishDate = $realFinishDate;
                }
            } else {
                throw new C3op_Projects_ActionException("This ($realFinishDate) is not a valid date of finish.");
            }
        }
    } //SetRealFinishDate

    public function getReceiptDate(C3op_Projects_ActionMapper $actionMapper)
    {

        if (is_null($this->receiptDate)) {
            $actionMapper->FetchLastReceiptDate($this);
        }

        return $this->receiptDate;
    } //GetRealFinishDate


}