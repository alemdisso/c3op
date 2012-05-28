<?php

class C3op_Projects_SubordinationManager {
    
    public function GetAllActionsSubordinatedTo(C3op_Projects_Action $action, 
            C3op_Projects_ActionMapper $actionMapper,
            C3op_Projects_Project $project,
            C3op_Projects_ProjectMapper $projectMapper
            )
    {
        $allActionsBelowMe = array();
        $actionsIdJustBelowMe = $actionMapper->getActionsSubordinatedTo($action);
        while (list(,$actionId) = each($actionsIdJustBelowMe)) {
            $thisAction = $actionMapper->findById($actionId);
            $actionsJustBelowMe = $this->GetAllActionsSubordinatedTo($thisAction, $actionMapper, $project, $projectMapper);
            $allActionsBelowMe[] = array("action" => $thisAction,
                      "actionsBelow"  => $actionsJustBelowMe
                    );
        }
        return $allActionsBelowMe;
    }
    

    
}
