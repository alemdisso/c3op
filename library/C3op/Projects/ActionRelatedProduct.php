<?php

class C3op_Projects_ActionRelatedProduct {

    private $action;
    private $actionMapper;
    private $actionsAbove;


    public function __construct(C3op_Projects_Action $action, C3op_Projects_ActionMapper $actionMapper)
    {
        $this->action = $action;
        $this->actionMapper = $actionMapper;
        $tree = new C3op_Projects_ActionsAbove($action, $actionMapper);
        $this->actionsAbove = $tree->retrieve();

    }

    public function retrieve()
    {
        $actionsAbove = $this->actionsAbove;
        if (is_array($actionsAbove)) {
            $howManyLevels = count($actionsAbove);
            if ($howManyLevels > 0) {
                $product = $this->actionMapper->findById($actionsAbove[$howManyLevels - 1]);
                return $product;
            } else {
                return $this->action;
            }

        } else {
            throw new C3op_Projects_ActionException('something wrong ocurred');
        }


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


