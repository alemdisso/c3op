<?php

class C3op_Projects_EventLogger {
    
    public function init()
    {
    }

    public function LogActionEvent(C3op_Projects_Action $action, $type)
    {
        $event = new C3op_Projects_ActionEvent($action);
        $event->SetType($type);
        $eventMapper = new C3op_Projects_ActionEventMapperBase($action);
        $eventMapper->insert($event);
    }
    
}

