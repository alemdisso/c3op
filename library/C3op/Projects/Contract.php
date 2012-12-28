<?php

class C3op_Projects_Contract {

    protected $id;
    protected $project;
    protected $amendment;
    protected $beginDate;
    protected $finishDate;
    protected $signingDate;
    protected $value;
    protected $contractNature;
    protected $managementFee;
    protected $object;
    protected $summary;
    protected $observation;

    function __construct($project, $signingDate, $amendment, $id=0) {
        $this->id = (int)$id;
        $this->project = $project;
        $this->beginDate = "";
        $this->finishDate = "";
        $this->value = 0;
        $this->contractNature = 0;
        $this->signingDate = $signingDate;
        $this->amendment = $amendment;
    }

    public function GetId() {
        return $this->id;

    } //GetId

    public function SetId($id) {
        if (($this->id == 0) && ($id > 0)) {
            $this->id = (int)$id;
        } else {
            throw new C3op_Projects_ContractException('It\'s not possible to change a contract\'s ID');
        }
    } //SetId

    public function GetProject()
    {
        return $this->project;
    }


    public function SetAmendment($amendment)
    {
        if ($amendment) {
            $this->amendment = true;
        } else {
            $this->amendment = false;
        }
    }

    public function GetAmendment()
    {
        return $this->amendment;

    }

    public function GetBeginDate()
    {
        return $this->beginDate;
    } //GetBeginDate

    public function SetBeginDate($beginDate)
    {
        if ($beginDate != "") {
            $dateValidator = new C3op_Util_ValidDate();

            if (($beginDate == "0000-00-00") || ($dateValidator->isValid($beginDate))) {
                if ($this->beginDate != $beginDate) {
                    $this->beginDate = $beginDate;
                }
            } else {
                throw new C3op_Projects_ContractException("This ($beginDate) is not a valid date of begin.");
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
            if (($finishDate == "0000-00-00") || ($dateValidator->isValid($finishDate))) {
                if ($this->finishDate != $finishDate) {
                    $this->finishDate = $finishDate;
                }
            } else {
                throw new C3op_Projects_ContractException("This ($finishDate) is not a valid date of finish.");
            }
        }
    } //SetFinishDate

    public function GetSigningDate()
    {
        return $this->signingDate;

    } //GetSigningDate

    public function SetSigningDate($signingDate)
    {

        $dateValidator = new C3op_Util_ValidDate();
        if ($dateValidator->isValid($signingDate)) {
            if (($signingDate == "0000-00-00") || ($dateValidator->isValid($signingDate))) {
                $this->signingDate = $signingDate;
            }
        } else {
            throw new C3op_Projects_ContractException("This ($signingDate) is not a valid date of begin.");
        }
    } //SetSigningDate

    public function SetValue($value)
    {
        $validator = new C3op_Util_ValidPositiveDecimal();

        if (($value == 0) || ($validator->isValid($value))) {
            $this->value = (float) $value;
        } else {
            throw new C3op_Projects_ContractException("Value must be a positive number.");
        }
    }

    public function GetValue()
    {
        return $this->value;
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
                $this->contractNature = null;
                break;

            default:
                throw new C3op_Projects_ContractException("Invalid contract nature.");
                break;
        }
    }

    public function SetManagementFee($managementFee)
    {
        $validator = new C3op_Util_ValidPositiveDecimal();

        if (($managementFee == 0) || ($validator->isValid($managementFee))) {
            $this->managementFee = (float) $managementFee;
        } else {
            throw new C3op_Projects_ContractException("Management Fee must be a positive number.");
        }
    }

    public function getManagementFee()
    {
        return $this->managementFee;
    }

    public function getObject()
    {
        return $this->object;
    } //getObject

    public function SetObject($object)
    {
        $validator = new C3op_Util_ValidLongString();
        if ((is_null($object)) || ($validator->isValid($object))) {
            if ($this->object != $object) {
                $this->object = $object;
            }
        } else {
            throw new C3op_Projects_ContractException("This ($object) is not a valid object.");
        }
    } //SetObject

    public function getSummary()
    {
        return $this->summary;
    } //getSummary

    public function SetSummary($summary)
    {
        $validator = new C3op_Util_ValidLongString();
        if ((is_null($summary)) || ($validator->isValid($summary))) {
            if ($this->summary != $summary) {
                $this->summary = $summary;
            }
        } else {
            throw new C3op_Projects_ContractException("This ($summary) is not a valid summary.");
        }
    } //SetSummary

    public function getObservation()
    {
        return $this->observation;
    } //getObservation

    public function SetObservation($observation)
    {
        $validator = new C3op_Util_ValidLongString();
        if ((is_null($observation)) || ($validator->isValid($observation))) {
            if ($this->observation != $observation) {
                $this->observation = $observation;
            }
        } else {
            throw new C3op_Projects_ContractException("This ($observation) is not a valid observation.");
        }
    } //SetObservation


}