<?php

class C3op_Projects_ActionsAbove {

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


        $this->getActionsAbove($this->action);
        return $this->actionsArray;

    }



    private function getActionsAbove(C3op_Projects_Action $action)
    {
        if ($action->getSubordinatedTo() > 0) {
            $this->actionsArray[] = $action->getSubordinatedTo();
            $parentAction = $this->actionMapper->findById($action->getSubordinatedTo());
            $this->getActionsAbove($parentAction);
        }
    }

}


