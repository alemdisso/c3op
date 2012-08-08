<?php

class C3op_Projects_ActionTree {
    
    private $tree;
    
    public function retrieveTree(C3op_Projects_Action $action, 
                                   C3op_Projects_ActionMapper $actionMapper)
    {
        // get all subordinated actions
        return($this->getTreeOfAllSubordinatedActions($action, $actionMapper));
    }
    
    private function getTreeOfAllSubordinatedActions(C3op_Projects_Action $action, 
                                   C3op_Projects_ActionMapper $actionMapper)
    {
        $tree = array();
        $subordinatedIds = $actionMapper->getActionsSubordinatedTo($action);
        $a = $action->GetId();

        
        if (count($subordinatedIds) > 0) {
            foreach ($subordinatedIds as $key => $id) {
                $loopAction = $actionMapper->findById($id);
                $tree[$id] = $this->getTreeOfAllSubordinatedActions($loopAction, $actionMapper);
            }
        }
        return $tree;
    }
    
    
        
    
}


