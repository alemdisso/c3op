<?php

class C3op_Projects_MaterialSupplyRemoval {

    private $materialSupply;
    private $materialSupplyMapper;

    public function __construct(C3op_Projects_MaterialSupply $materialSupply, C3op_Projects_MaterialSupplyMapper $materialSupplyMapper)
    {
        $this->materialSupply = $materialSupply;
        $this->materialSupplyMapper = $materialSupplyMapper;
    }

    public function canBeRemoved()
    {
        $can=true;
        if ($this->materialSupplyHasOutlays()) {
            $can = false;
        }

        if ($this->materialSupplyWasContracted()) {
            $can = false;
        }

        return $can;

    }

    public function remove()
    {
        //check if can really remove
        if ($this->canBeRemoved()) {

            // delete materialSupply
            $this->materialSupplyMapper->delete($this->materialSupply);

        } else {
            throw new C3op_Projects_MaterialSupplyException("This Team Member can't be removed");
        }



    }

    private function materialSupplyHasOutlays()
    {
        $result = $this->materialSupplyMapper->getAllOutlays($this->materialSupply);

        if (count($result)) {
            return true;
        } else {
            return false;
        }
    }

    private function materialSupplyWasContracted()
    {
        $status = $this->materialSupply->getStatus();

        if (($status == C3op_Projects_MaterialSupplyStatusConstants::STATUS_CONTRACTED)
                || ($status == C3op_Projects_MaterialSupplyStatusConstants::STATUS_ACQUITTED)) {
            return true;
        } else {
            return false;
        }
    }

}
