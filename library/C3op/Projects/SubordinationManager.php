<?php

class C3op_Projects_SubordinationManager {
    
    public function GetAllActionsSubordinatedTo(C3op_Projects_Action $action, 
            C3op_Projects_ActionMapper $actionMapper,
            C3op_Projects_Project $project,
            C3op_Projects_ProjectMapper $projectMapper
            )
    {
        echo "<hr>entering getallactionssubordinatedto<br>";
        echo "action " . $action->GetId() . " - " . $action->GetTitle() . "<br>";
        echo "project " . $project->GetId() . "<br>";
        
        
        $allActionsBelowMe = array();
        $actionsIdJustBelowMe = $actionMapper->getActionsSubordinatedTo($action);
        while (list(,$actionId) = each($actionsIdJustBelowMe)) {
            $thisAction = $actionMapper->findById($actionId);
            $actionsJustBelowMe = $this->GetAllActionsSubordinatedTo($thisAction, $actionMapper, $project, $projectMapper);
            $allActionsBelowMe[] = array("action" => $thisAction,
                      "actionsBelow"  => $actionsJustBelowMe
                    );
        }
        echo "returning from getallactionssubordinatedto<br>";
        echo "action " . $action->GetId() . " - " . $action->GetTitle() . "<br>";
        echo "project " . $project->GetId() . "<br>";
        print_r($allActionsBelowMe);
        echo "count allactionsbelowme " . count($allActionsBelowMe) . "<br><hr><br>";
        
        
        return $allActionsBelowMe;
    }
    

    
}
