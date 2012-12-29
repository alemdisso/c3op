<?php

class C3op_Projects_ProjectSeemsToBeFinished {

    private $project;

    function __construct(C3op_Projects_Project $project)
    {
            $this->project = $project;
    }

    public function seemsToBeFinished()
    {
        if (($this->doesTheFinishDateHasPast())
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

    private function doesTheFinishDateHasPast()
    {

        if (($this->doesItHasAFinishDate())) {
            $today = strtotime(date("Y-m-d"));
            $finish = strtotime($this->project->getFinishDate());
            if ($today > $finish) {
                return true;
            }

        }
        return false;

    }

}

