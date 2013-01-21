<?php

class C3op_Resources_OutsideServiceRemoval {

    private $outsideService;
    private $outsideServiceMapper;

    public function __construct(C3op_Resources_OutsideService $outsideService, C3op_Resources_OutsideServiceMapper $outsideServiceMapper)
    {
        $this->outsideService = $outsideService;
        $this->outsideServiceMapper = $outsideServiceMapper;
    }

    public function canBeRemoved()
    {
        $can=true;
        if ($this->outsideServiceHasOutlays()) {
            $can = false;
        }

        if ($this->outsideServiceWasContracted()) {
            $can = false;
        }

        return $can;

    }

    public function remove()
    {
        //check if can really remove
        if ($this->canBeRemoved()) {

            // delete outsideService
            $this->outsideServiceMapper->delete($this->outsideService);

        } else {
            throw new C3op_Resources_OutsideServiceException("This Team Member can't be removed");
        }



    }

    private function outsideServiceHasOutlays()
    {
        return false;

        $result = $this->outsideServiceMapper->getAllOutlays($this->outsideService);

        if (count($result)) {
            return true;
        } else {
            return false;
        }
    }

    private function outsideServiceWasContracted()
    {
        $status = $this->outsideService->getStatus();

        if (($status == C3op_Resources_OutsideServiceStatusConstants::STATUS_CONTRACTED)
                || ($status == C3op_Resources_OutsideServiceStatusConstants::STATUS_ACQUITTED)) {
            return true;
        } else {
            return false;
        }
    }

}
