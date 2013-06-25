<?php

class C3op_Projects_ProjectStatusTypes {

    private $titles = array();


    public function __construct() {
        $this->titles = array(

            C3op_Projects_ProjectStatusConstants::STATUS_NIL            => _("#Inexistente"),
            C3op_Projects_ProjectStatusConstants::STATUS_PROSPECTING    => _("#Prospecting"),
            C3op_Projects_ProjectStatusConstants::STATUS_PLANNING       => _("#project.status.type.Planning"),
            C3op_Projects_ProjectStatusConstants::STATUS_PROPOSAL       => _("#Proposal"),
            C3op_Projects_ProjectStatusConstants::STATUS_EXECUTION      => _("#Execution"),
            C3op_Projects_ProjectStatusConstants::STATUS_ACCOUNTABILITY => _("#Accountability"),
            C3op_Projects_ProjectStatusConstants::STATUS_CANCELED       => _("#Canceled"),
            C3op_Projects_ProjectStatusConstants::STATUS_SUSPENDED      => _("#Suspended"),
            C3op_Projects_ProjectStatusConstants::STATUS_FINISHED       => _("#Finished"),
        );
    }

    public function TitleForType($type)
    {
            switch ($type) {
                case C3op_Projects_ProjectStatusConstants::STATUS_PROSPECTING:
                case C3op_Projects_ProjectStatusConstants::STATUS_PLANNING:
                case C3op_Projects_ProjectStatusConstants::STATUS_PROPOSAL:
                case C3op_Projects_ProjectStatusConstants::STATUS_EXECUTION:
                case C3op_Projects_ProjectStatusConstants::STATUS_ACCOUNTABILITY:
                case C3op_Projects_ProjectStatusConstants::STATUS_CANCELED:
                case C3op_Projects_ProjectStatusConstants::STATUS_SUSPENDED:
                case C3op_Projects_ProjectStatusConstants::STATUS_FINISHED:
                    return $this->titles[$type];
                    break;

                default:
                    return _("#Unknown type");
                    break;
            }
    }

    public function AllTitles($includeNull = false)
    {

        if ($includeNull) {
            return $this->titles;
        } else {
            $data = array();
            foreach ($this->titles as $k => $v) {
                if ($k != C3op_Projects_ProjectStatusConstants::STATUS_NIL) {
                    $data[$k] = $v;
                }
            }
            return($data);
        }
    }
}