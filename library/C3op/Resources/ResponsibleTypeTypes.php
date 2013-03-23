<?php

class C3op_Resources_ResponsibleTypeTypes {

    private $types = array();


    public function __construct() {
        $this->types = array(
            C3op_Resources_ResponsibleTypeConstants::TYPE_NIL => _("#Nil"),
            C3op_Resources_ResponsibleTypeConstants::TYPE_TEAM_MEMBER => _("#Team member"),
            C3op_Resources_ResponsibleTypeConstants::TYPE_OUTSIDE_SERVICE => _("#Outside service"),
        );
    }

    public function TitleForType($type)
    {
            switch ($type) {
                case C3op_Resources_ResponsibleTypeConstants::TYPE_NIL:
                case C3op_Resources_ResponsibleTypeConstants::TYPE_TEAM_MEMBER:
                case C3op_Resources_ResponsibleTypeConstants::TYPE_OUTSIDE_SERVICE:
                    return $this->types[$type];
                    break;

                default:
                    return _("#Unknown type");
                    break;
            }
    }

    public function AllTypes()
    {
        return $this->types;
    }
}