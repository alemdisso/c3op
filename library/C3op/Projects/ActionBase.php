<?php

class C3op_Projects_ActionBase {
	
    protected $id;
    protected $title = "";
    protected $project;
    protected $milestone = false;
    protected $requirementForReceiving = false;
    protected $subordinatedTo = 0;
    
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

    public function SetSubordinatedTo($subordinatedTo) 
    {
        $this->subordinatedTo = $subordinatedTo;
    }
    
    public function GetSubordinatedTo()
    {
        return $this->subordinatedTo;
    }
}