<?php

class C3op_Projects_ProjectSeemsToBeInExecution {

    private $project;

    function __construct(C3op_Projects_Project $project)
    {
            $this->project = $project;
    }

    public function seemsToBeInExecution()
    {
        if (($this->doesItSeemsToBeHappeningNow())
            && ($this->doesItSeemsToBeContracted()))
        {
            return true;
        } else {
            return false;

        }
    }

    private function doesItSeemsToBeContracted()
    {
        $doesIt = new C3op_Projects_ProjectSeemsToBeContracted($this->project);
        return $doesIt->seemsToBeContracted();
    }


    private function doesItHasAFinishDate()
    {
        $validator = new C3op_Util_ValidDate();

        if ($validator->isValid($this->project->getFinishDate())) {
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

    private function doesItSeemsToBeHappeningNow()
    {

        if (($this->doesItHasABeginDate()) && ($this->doesItHasAFinishDate())) {
            $today = strtotime(date("Y-m-d"));
            $begin = strtotime($this->project->getBeginDate());
            $finish = strtotime($this->project->getFinishDate());
            if (($today > $begin) && ($today < $finish)) {
                return true;
            }

        }
        return false;

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

