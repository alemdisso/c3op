<?php

class C3op_Projects_ProjectSeemsToBeContracted {

    private $project;

    function __construct(C3op_Projects_Project $project)
    {
            $this->project = $project;
    }

    public function seemsToBeContracted()
    {
        if (($this->doesItHasABeginDate())
            && ($this->doesItHasAClient())
            && ($this->doesItHasAValue()))
        {
            return true;
        } else {
            return false;

        }
    }


    private function doesItHasABeginDate()
    {
        $validator = new C3op_Util_ValidDate();

        if ($validator->isValid($this->project->getBeginDate())) {
            return true;
        } else {
            return false;
        }

    }

    private function doesItHasAClient()
    {
        $validator = new C3op_Util_ValidPositiveInteger();

        if (($validator->isValid($this->project->getClient()))
             && ($this->project->getClient() > 0)){
            return true;
        } else {
            return false;
        }

    }

    private function doesItHasAValue()
    {
        $validator = new C3op_Util_ValidPositiveDecimal();

        if ($validator->isValid($this->project->getValue())) {
            return true;
        } else {
            return false;
        }

    }

}

