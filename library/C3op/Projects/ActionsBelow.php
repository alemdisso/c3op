<?php

class C3op_Projects_ActionsBelow {

    private $action;
    private $actionMapper;
    private $actionsArray;

    public function __construct(C3op_Projects_Action $action, C3op_Projects_ActionMapper $actionMapper)
    {
        $this->action = $action;
        $this->actionMapper = $actionMapper;
        $this->actionsArray = array();

    }

    public function retrieve()
    {


        $this->getActionsBelow($this->action);
        return $this->actionsArray;

    }



    private function getActionsBelow(C3op_Projects_Action $action)
    {

        $result = $this->actionMapper->getActionsSubordinatedTo($action);
        foreach ($result as $id) {
            $this->actionsArray[] = $id;
            $childAction = $this->actionMapper->findById($id);
            $this->getActionsBelow($childAction);

        }

    }

}


