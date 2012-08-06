<?php

class C3op_Projects_ActionCheckStart {
    
    
    
    
    public function __construct(C3op_Projects_Action $action, 
                                   C3op_Projects_ActionMapper $actionMapper)
    {
        if ($this->shouldStarted($action)) {
            $this->start($action, $actionMapper);
        }
        
    }
        
    
    public function shouldStarted(C3op_Projects_Action $action) {
        
        $dateValidator = new C3op_Util_ValidDate();
        if ($dateValidator->isValid($action->getPredictedBeginDate())){
            if (($action->getStatus() == C3op_Projects_ActionStatusConstants::STATUS_PLAN) 
                    && ($action->getPredictedBeginDate() <= date("Y-m-d 0:0:0"))){
                return true;
            }
        }
        return false;
    }
    
    public function start(C3op_Projects_Action $action, 
                          C3op_Projects_ActionMapper $actionMapper)
    {
            $action->SetRealBeginDate(date("Y-m-d"));
            $action->SetStatus(C3op_Projects_ActionStatusConstants::STATUS_IN_EXECUTION);
            $actionMapper->update($action);

            $this->LogAutoStart($action);
    
    }
    
    private function LogAutoStart(C3op_Projects_Action $action)
    {
        $logger = new C3op_Projects_EventLogger();
        $logger->LogActionEvent($action, 
                C3op_Projects_ActionEventConstants::EVENT_BEGIN_AUTOMATICALLY, "auto check");
    }
    
}


