<?php

class C3op_Projects_MaterialSupplyHasCredit {

    private $materialSupply;
    private $mapper;

    function __construct(C3op_Projects_MaterialSupply $materialSupply, C3op_Projects_MaterialSupplyMapper $mapper)
    {
            $this->materialSupply = $materialSupply;
            $this->mapper = $mapper;
    }

    public function hasCredit()
    {
        $budget = $this->materialSupply->getValue();
        $payed = $this->mapper->getSumOfPayedOutlays($this->materialSupply);
        $provided = $this->mapper->getSumOfProvidedButNotPayedOutlays($this->materialSupply);

        if ($budget > ($payed + $provided))
        {
            return true;
        } else {
            return false;

        }
    }


}

