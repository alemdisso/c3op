<?php

class C3op_Projects_ActionStartMode {

    private $action;
    private $actionMapper;

    public function __construct(C3op_Projects_Action $action, C3op_Projects_ActionMapper $actionMapper)
    {
        $this->action = $action;
        $this->actionMapper = $actionMapper;
    }

    public function isUnacknowledged()
    {
        $lastAutoStart = $this->actionMapper->GetLastAutoStartDate($this->action);
        if (!is_null($lastAutoStart)) {
            $lastAcknowledgment = $this->actionMapper->GetLastAcknowledgeStartDate($this->action);
            $lastCancel = $this->actionMapper->GetLastCancelStartDate($this->action);
            if ((is_null($lastAcknowledgment)) && (is_null($lastCancel))) {
                return true;
            } else {
                if (!(is_null($lastAcknowledgment)) && ($lastAcknowledgment < $lastAutoStart)) {
                    return true;
                }
                if (!(is_null($lastCancel)) && ($lastCancel < $lastAutoStart)) {
                    return true;
                }
            }
        }

        return false;
    }
}
