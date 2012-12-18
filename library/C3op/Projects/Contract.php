<?php

class C3op_Projects_Contract {

    protected $id;
    protected $project;
    protected $beginDate;
    protected $finishDate;
    protected $value;
    protected $contractNature;
    protected $title;
    protected $signingDate;
    protected $amendment;

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

    public function SetProject($project)
    {
        if ($this->project != $project) {
            $validator = new C3op_Util_ValidPositiveInteger();
            if ($validator->isValid($project)) {
                $this->project = $project;
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
            if (($beginDate == "0000-00-00") || ($dateValidator->isValid($beginDate))) {
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
            if (($finishDate == "0000-00-00") || ($dateValidator->isValid($finishDate))) {
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
                throw new C3op_Projects_ProjectException("Invalid contract nature.");
                break;
        }
    }

    public function GetTitle()
    {
        return $this->title;
    } //GetTitle

    public function SetTitle($title)
    {
        $validator = new C3op_Util_ValidString();
        if ($validator->isValid($title)) {
            if ($this->title != $title) {
                $this->title = $title;
            }
        } else {
            throw new C3op_Projects_ContractException("This ($title) is not a valid title.");
        }

    } //SetTitle

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


}