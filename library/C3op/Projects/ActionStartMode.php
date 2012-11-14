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
            if (is_null($lastAcknowledgment)) {
                return true;
            } else {
                if ($lastAcknowledgment < $lastAutoStart) {
                    return true;
                }
            }
        }

        return false;
    }
}
