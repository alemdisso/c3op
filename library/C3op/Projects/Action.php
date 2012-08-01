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

    public function GetId()
    {
        return $this->id;

    } //GetId

    public function SetId($id)
    {
        if (($this->id == 0) && ($id > 0)) {
            $this->id = (int)$id;
        } else {
            throw new C3op_Projects_ActionException('It\'s not possible to change a action\'s ID');
        }

    } //SetId

    public function GetTitle() 
    {
        return $this->title;

    } //GetTitle
	
    public function SetTitle($title) 
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

    public function GetProject()
    {
        return $this->project;
    }
	
    public function SetProject($project) 
    {
        $this->project = $project;
    }
    
    public function SetDone($done) 
    {
        if ($done) {
            $this->done = $done;
        } else {
            $this->done = 0;
        }
    }
    
    public function GetDone()
    {
        return $this->done;
    }
    
    public function GetStatus() 
    {
        return $this->status;
    }
    
    public function SetStatus($status) 
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
    
    public function GetDescription()
    {
        return $this->description;
    } //GetDescription
	
    public function SetDescription($description)
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

    public function GetBegin()
    {
        return $this->begin;
    } //GetBegin
	
    public function SetBegin($begin)
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

    public function SetSubordinatedTo($subordinatedTo) 
    {
        $this->subordinatedTo = $subordinatedTo;
    }
    
    public function GetSubordinatedTo()
    {
        return $this->subordinatedTo;
    }
    
    
    public function GetResponsible()
    {
        return $this->responsible;
    }
	
    public function SetResponsible($responsible) 
    {
        if ($this->responsible != $responsible) {
            $validator = new C3op_Util_ValidPositiveInteger();
            if ($validator->isValid($responsible)) {
                $this->responsible = $responsible;
            }
        }
    }
    
    public function SetMilestone($milestone) 
    {
        if ($milestone) {
            $this->milestone = $milestone;
        } else {
            $this->milestone = 0;
        }
    }
    
    public function GetMilestone()
    {
        return $this->milestone;
    }
    
    public function SetRequirementForReceiving($requirementForReceiving) 
    {
        if ($requirementForReceiving) {
            $this->requirementForReceiving = $requirementForReceiving;
        } else {
            $this->requirementForReceiving = 0;
        }
    }
    
    public function GetRequirementForReceiving()
    {
        return $this->requirementForReceiving;
    }
    
    public function GetPredictedBeginDate()
    {
        return $this->predictedBeginDate;
    } //GetPredictedBeginDate
	
    public function SetPredictedBeginDate($predictedBeginDate)
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

    public function GetPredictedFinishDate()
    {
        return $this->predictedFinishDate;
    } //GetPredictedFinishDate
	
    public function SetPredictedFinishDate($predictedFinishDate)
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

    public function GetRealBeginDate()
    {
        return $this->realBeginDate;
    } //GetRealBeginDate
	
    public function SetRealBeginDate($realBeginDate)
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

    public function GetRealFinishDate()
    {
        return $this->realFinishDate;
    } //GetRealFinishDate
	
    public function SetRealFinishDate($realFinishDate)
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

    public function Realize() 
    {
        $this->SetDone(true);
        
    }
    
    public function GetReceiptDate(C3op_Projects_ActionMapper $actionMapper)
    {
 
        if (is_null($this->receiptDate)) {
            $actionMapper->FetchLastReceiptDate($this);
        }
        
        return $this->receiptDate;
    } //GetRealFinishDate
	
    
}