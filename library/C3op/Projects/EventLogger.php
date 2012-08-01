<?php

class C3op_Projects_EventLogger {
    
    public function init()
    {
    }

    public function LogActionEvent(C3op_Projects_Action $action, $type, $observation = "")
    {
        $event = new C3op_Projects_ActionEvent($action);
        $event->SetType($type);
        if ($observation != "") {
            $event->SetObservation($observation);
        }
        $eventMapper = new C3op_Projects_ActionEventMapper($action);
        $eventMapper->insert($event);
    }
    
}

