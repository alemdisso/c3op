<?php

class C3op_Projects_ProjectTree {
    
    private $tree;
    
    public function retrieveTree(C3op_Projects_Project $project, 
                                C3op_Projects_ProjectMapper $projectMapper,
                                C3op_Projects_ActionMapper $actionMapper)
    {
        $mainActions = $projectMapper->getAllActionsSubordinatedTo($project);
        
        
        $projectTree = array();
        
        foreach ($mainActions as $actionId) {
            
            $loopAction = $actionMapper->findById($actionId);
            $obj = new C3op_Projects_ActionTree();
            $actionTree = $obj->retrieveTree($loopAction, $actionMapper);
            $projectTree[$actionId] = $actionTree;
        }
        
        // get all subordinated actions
        return($projectTree);
    }
    
    
        
    
}


