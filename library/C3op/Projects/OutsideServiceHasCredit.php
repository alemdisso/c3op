<?php

class C3op_Projects_OutsideServiceHasCredit {

    private $outsideService;
    private $mapper;

    function __construct(C3op_Projects_OutsideService $outsideService, C3op_Projects_OutsideServiceMapper $mapper)
    {
            $this->outsideService = $outsideService;
            $this->mapper = $mapper;
    }

    public function hasCredit()
    {
        return false;
        $budget = $this->outsideService->getValue();
        $payed = $this->mapper->getSumOfPayedOutlays($this->outsideService);
        $provided = $this->mapper->getSumOfProvidedButNotPayedOutlays($this->outsideService);

        if ($budget > ($payed + $provided))
        {
            return true;
        } else {
            return false;

        }
    }


}

