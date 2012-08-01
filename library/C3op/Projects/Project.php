<?php

class C3op_Projects_Project
{
	
    protected $id;
    protected $title;
    protected $client;
    protected $ourResponsible;
    protected $responsibleAtClient;
    protected $beginDate;
    protected $finishDate;
    protected $status;
    protected $value;
    protected $contractNature;
    protected $areaActivity;
    protected $overhead;
    protected $managementFee;
    protected $object;
    protected $summary;
    protected $observation;
    
	
    function __construct($id=0) {
        $this->id = (int)$id;
        $this->title = "";
        $this->beginDate = "";
        $this->finishDate = "";
        $this->value = 0;
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
            throw new C3op_Projects_ProjectException('It\'s not possible to change a project\'s ID');
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
            throw new C3op_Projects_ProjectException("This ($title) is not a valid title.");
        }
    } //SetTitle

    public function GetClient()
    {
        return $this->client;
    }
	
    public function SetClient($client) 
    {
        if ($this->client != $client) {
            $validator = new C3op_Util_ValidPositiveInteger();
            if ($validator->isValid($client)) {
                $this->client = $client;
            }
        }
    }

    public function GetOurResponsible()
    {
        return $this->ourResponsible;
    }
	
    public function SetOurResponsible($ourResponsible) 
    {
        if ($this->ourResponsible != $ourResponsible) {
            $validator = new C3op_Util_ValidPositiveInteger();
            if ($validator->isValid($ourResponsible)) {
                $this->ourResponsible = $ourResponsible;
            }
        }
    }
    
    public function GetResponsibleAtClient()
    {
        return $this->responsibleAtClient;
    }
	
    public function SetResponsibleAtClient($responsibleAtClient) 
    {
        if ($this->responsibleAtClient != $responsibleAtClient) {
            $validator = new C3op_Util_ValidPositiveInteger();
            if ($validator->isValid($responsibleAtClient)) {
                $this->responsibleAtClient = $responsibleAtClient;
            }
        }
    }
    
    public function GetBeginDate()
    {
        return $this->beginDate;
    } //GetBeginDate
	
    public function SetBeginDate($beginDate)
    {
        if ($beginDate != "") {
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($beginDate)) {
                if ($this->beginDate != $beginDate) {
                    $this->beginDate = $beginDate;
                }
            } else {
                throw new C3op_Projects_ProjectException("This ($beginDate) is not a valid date of begin.");
            }
        }
    } //SetBeginDate

    public function GetFinishDate()
    {
        return $this->finishDate;
    } //GetFinishDate
	
    public function SetFinishDate($finishDate)
    {
        if ($finishDate != "") {
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($finishDate)) {
                if ($this->finishDate != $finishDate) {
                    $this->finishDate = $finishDate;
                }
            } else {
                throw new C3op_Projects_ProjectException("This ($finishDate) is not a valid date of finish.");
            }
        }
    } //SetFinishDate

    public function SetValue($value) 
    {
        $validator = new C3op_Util_ValidPositiveFloat();
        
        if ($validator->isValid($value)) {
            $this->value = (float) $value;
        } else {
            throw new C3op_Projects_ProjectException("Value must be a positive number.");
        }
    }

    public function GetValue() 
    {
        return $this->value;
    }

    public function SetStatus($status) 
    {
        $validator = new C3op_Util_ValidPositiveFloat();
        
        if ($validator->isValid($status)) {
            $this->status = (float) $status;
        } else {
            throw new C3op_Projects_ProjectException("Status must be a positive number.");
        }
    }

    public function GetStatus() 
    {
        return $this->status;
    }
    
    public function GetContractNature() 
    {
        return $this->contractNature;
    }
    
    public function SetContractNature($contractNature) 
    {
        switch ($contractNature) {
            case C3op_Projects_ContractNatureConstants::NATURE_CONTRACT:
            case C3op_Projects_ContractNatureConstants::NATURE_AGREEMENT:
            case C3op_Projects_ContractNatureConstants::NATURE_PARTNERSHIP_TERM:
            case C3op_Projects_ContractNatureConstants::NATURE_DONATION:
            case C3op_Projects_ContractNatureConstants::NATURE_SPONSORSHIP:
            case C3op_Projects_ContractNatureConstants::NATURE_COOPERATION:
            case C3op_Projects_ContractNatureConstants::NATURE_OTHER:
                $this->contractNature = (int)$contractNature;
                break;
            
            case null:
            case "":
            case 0:
            case false:
                $this->type = null;
                break;
                 
            default:
                throw new C3op_Projects_ProjectException("Invalid contract nature.");
                break;
        }
    }
    
    public function GetAreaActivity()
    {
        return $this->areaActivity;
    }
	
    public function SetAreaActivity($areaActivity) 
    {
        if ($this->areaActivity != $areaActivity) {
            $validator = new C3op_Util_ValidPositiveInteger();
            if ($validator->isValid($areaActivity)) {
                $this->areaActivity = $areaActivity;
            }
        }
    }
        
    public function SetOverhead($overhead) 
    {
        $validator = new C3op_Util_ValidPositiveFloat();
        
        if ($validator->isValid($overhead)) {
            $this->overhead = (float) $overhead;
        } else {
            throw new C3op_Projects_ProjectException("Overhead must be a positive number.");
        }
    }

    public function GetOverhead() 
    {
        return $this->overhead;
    }

    public function SetManagementFee($managementFee) 
    {
        $validator = new C3op_Util_ValidPositiveFloat();
        
        if ($validator->isValid($managementFee)) {
            $this->managementFee = (float) $managementFee;
        } else {
            throw new C3op_Projects_ProjectException("Management Fee must be a positive number.");
        }
    }

    public function GetManagementFee() 
    {
        return $this->managementFee;
    }
    
    public function GetObject()
    {
        return $this->object;
    } //GetObject
	
    public function SetObject($object)
    {
        $validator = new C3op_Util_ValidLongString();
        if ($validator->isValid($object)) {
            if ($this->object != $object) {
                $this->object = $object;
            }
        } else {
            throw new C3op_Projects_ProjectException("This ($object) is not a valid object.");
        }
    } //SetObject

    public function GetSummary()
    {
        return $this->summary;
    } //GetSummary
	
    public function SetSummary($summary)
    {
        $validator = new C3op_Util_ValidLongString();
        if ($validator->isValid($summary)) {
            if ($this->summary != $summary) {
                $this->summary = $summary;
            }
        } else {
            throw new C3op_Projects_ProjectException("This ($summary) is not a valid summary.");
        }
    } //SetSummary

    public function GetObservation()
    {
        return $this->observation;
    } //GetObservation
	
    public function SetObservation($observation)
    {
        $validator = new C3op_Util_ValidLongString();
        if ($validator->isValid($observation)) {
            if ($this->observation != $observation) {
                $this->observation = $observation;
            }
        } else {
            throw new C3op_Projects_ProjectException("This ($observation) is not a valid observation.");
        }
    } //SetObservation

   
    
}