<?php

class C3op_Projects_ActionDateChange {
    
    public static function ChangePredictedBeginDate(C3op_Projects_Action $action, 
                                   C3op_Projects_ActionMapper $actionMapper,
                                   $newDate,
                                   $observation)
    {
        
        if (($observation != "") 
                && ($action->GetPredictedBeginDate() != $newDate)){
            
            $action->SetPredictedBeginDate($newDate);
            $actionMapper->update($action);

            self::LogPredictedBeginDateChanging($action, $observation);
        }
    
    }
    
    public static function ChangePredictedFinishDate(C3op_Projects_Action $action, 
                                   C3op_Projects_ActionMapper $actionMapper,
                                   $newDate,
                                   $observation)
    {
        if (($observation != "") 
                && ($action->GetPredictedFinishDate() != $newDate)){
            
            $action->SetPredictedFinishDate($newDate);
            $actionMapper->update($action);

            self::LogPredictedFinishDateChanging($action, $observation);
        }
    
    }
    
    private function LogPredictedBeginDateChanging(C3op_Projects_Action $action, $observation)
    {
        $logger = new C3op_Projects_EventLogger();
        $logger->LogActionEvent($action, 
                C3op_Projects_ActionEventConstants::EVENT_PLANNED_BEGIN_DATE_CHANGE,
                $observation);
    }
    
    private function LogPredictedFinishDateChanging(C3op_Projects_Action $action, $observation)
    {
        $logger = new C3op_Projects_EventLogger();
        $logger->LogActionEvent($action, 
                C3op_Projects_ActionEventConstants::EVENT_PLANNED_FINISH_DATE_CHANGE,
                $observation);
    }
}


