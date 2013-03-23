<?php

class C3op_Resources_ResponsibleRemoval {

    private $responsible;
    private $responsibleMapper;

    public function __construct(C3op_Resources_Responsible $responsible, C3op_Resources_ResponsibleMapper $responsibleMapper)
    {
        $this->responsible = $responsible;
        $this->responsibleMapper = $responsibleMapper;
    }

    public function canBeRemoved()
    {
        $can=true;
        if ($this->responsibleHasOutlays()) {
            $can = false;
        }

        if ($this->responsibleWasContracted()) {
            $can = false;
        }

        return $can;

    }

    public function remove()
    {
        //check if can really remove
        if ($this->canBeRemoved()) {

            // delete responsible
            $this->responsibleMapper->delete($this->responsible);

        } else {
            throw new C3op_Resources_ResponsibleException("This Team Member can't be removed");
        }



    }

    private function responsibleHasOutlays()
    {
        $result = $this->responsibleMapper->getAllOutlays($this->responsible);

        if (count($result)) {
            return true;
        } else {
            return false;
        }
    }

    private function responsibleWasContracted()
    {
        $status = $this->responsible->getStatus();

        if (($status == C3op_Resources_ResponsibleStatusConstants::STATUS_CONTRACTED)
                || ($status == C3op_Resources_ResponsibleStatusConstants::STATUS_ACQUITTED)) {
            return true;
        } else {
            return false;
        }
    }

}
