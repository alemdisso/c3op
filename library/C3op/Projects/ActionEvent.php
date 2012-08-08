<?php

class C3op_Projects_ActionEvent {
	
    protected $id;
    protected $action;
    protected $done=false;
    protected $type=0;
    protected $observation;
    protected $responsible = 0;
    
    function __construct($action, $id=0)
    {
        $this->action = $action;
        $this->id = (int)$id;

    }

    public function GetId()
    {
        return $this->id;

    } //GetId

    public function SetId($id)
    {
        if (($this->id == 0) && ($id > 0)) {
            $this->id = (int)$id;
        } else {
            throw new C3op_Projects_ActionEventException('It\'s not possible to change a action\'s  event\'sID');
        }

    } //SetId

    public function GetAction()
    {
        return $this->action;

    } //GetAction

    public function SetAction($action)
    {
        if (($this->action == 0) && ($action > 0)) {
            $this->action = (int)$action;
        } else {
            throw new C3op_Projects_ActionEventException('It\'s not possible to change an action\'s  event\'s action');
        }

    } //SetAction

    public function GetType() 
    {
        return $this->type;
    }
    
    public function SetType($type) 
    {
        switch ($type) {
            case C3op_Projects_ActionEventConstants::EVENT_PLANNING:
            case C3op_Projects_ActionEventConstants::EVENT_PLANNED_BEGIN_DATE_CHANGE:
            case C3op_Projects_ActionEventConstants::EVENT_PLANNED_FINISH_DATE_CHANGE:
            case C3op_Projects_ActionEventConstants::EVENT_CONTRACT_RESOURCE:
            case C3op_Projects_ActionEventConstants::EVENT_BEGIN_EXECUTION:
            case C3op_Projects_ActionEventConstants::EVENT_BEGIN_AUTOMATICALLY:
            case C3op_Projects_ActionEventConstants::EVENT_BEGIN_ACKNOWLEDGMENT:
            case C3op_Projects_ActionEventConstants::EVENT_ACKNOWLEDGE_RECEIPT:
            case C3op_Projects_ActionEventConstants::EVENT_CONFIRM_REALIZATION:
            case C3op_Projects_ActionEventConstants::EVENT_REGISTER_DELIVERY:
            case C3op_Projects_ActionEventConstants::EVENT_COMPLETE_ACTION:
            case C3op_Projects_ActionEventConstants::EVENT_DISMISS_RESOURCE:
            case C3op_Projects_ActionEventConstants::EVENT_HALT_EXECUTION:
            case C3op_Projects_ActionEventConstants::EVENT_REJECT_RECEIPT:
            case C3op_Projects_ActionEventConstants::EVENT_CANCEL_ACTION:
                $this->type = (int)$type;
                break;
            
            case null:
            case "":
            case 0:
            case false:
                $this->type = null;
                break;
                 
            default:
                throw new C3op_Projects_ActionEventException("Invalid action\'s event type.");
                break;
        }
    }
    
    public function GetObservation()
    {
        return $this->observation;
    } //GetObservation
	
    public function SetObservation($observation)
    {
        $validator = new C3op_Util_ValidLongString();
        if ($validator->isValid($observation)) {
            if ($this->observation != $observation) {
                $this->observation = $observation;
            }
        } else {
            throw new C3op_Projects_ActionEventException("This ($observation) is not a valid observation.");
        }
    } //SetObservation

    public function GetResponsible()
    {
        return $this->responsible;
    }
	
    public function SetResponsible($responsible) 
    {
        if ($this->responsible != $responsible) {
            $validator = new C3op_Util_ValidPositiveInteger();
            if ($validator->isValid($responsible)) {
                $this->responsible = $responsible;
            }
        }
    }
    
    public function GetTimestamp()
    {
        return $this->timestamp;
    }
	
}