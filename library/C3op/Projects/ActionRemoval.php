<?php

class C3op_Projects_ActionRemoval {

    private $action;
    private $actionMapper;

    public function __construct(C3op_Projects_Action $action, C3op_Projects_ActionMapper $actionMapper)
    {
        $this->action = $action;
        $this->actionMapper = $actionMapper;
    }

    public function canBeRemoved()
    {
        $can=true;
        if ($this->actionHasStaff()) {
            $can = false;
        }

        if ($this->actionHasSubordinatedActions()) {
            $can = false;
        }

        return $can;

    }

    public function remove()
    {
        //check if can really remove
        if ($this->canBeRemoved()) {

            // delete action
            $this->actionMapper->delete($this->action);

        } else {
            throw new C3op_Projects_ActionException("This action can't be removed");
        }



    }

    private function actionHasStaff()
    {
        $result = $this->actionMapper->getAnyTeamMemberRelatedTo($this->action);

        if (count($result)) {
            return true;
        } else {
            return false;
        }
    }

    private function actionHasSubordinatedActions()
    {
        $result = $this->actionMapper->getActionsSubordinatedTo($this->action);

        if (count($result)) {
            return true;
        } else {
            return false;
        }
    }

}
