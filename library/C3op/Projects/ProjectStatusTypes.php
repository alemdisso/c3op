<?php

class C3op_Projects_ProjectStatusTypes {

    private $titles = array();


    public function __construct() {
        $this->titles = array(

            C3op_Projects_ProjectStatusConstants::STATUS_PROSPECTING => _("#Prospecting"), //Prospecção
            C3op_Projects_ProjectStatusConstants::STATUS_PLANNING => _("#project.status.type.Planning"), //Planejamento
            C3op_Projects_ProjectStatusConstants::STATUS_PROPOSAL => _("#Proposal"), //Proposta
            C3op_Projects_ProjectStatusConstants::STATUS_EXECUTION => _("#Execution"), //Execução
            C3op_Projects_ProjectStatusConstants::STATUS_ACCOUNTABILITY => _("#Accountability"), //Prestação de contas
            C3op_Projects_ProjectStatusConstants::STATUS_CANCELED => _("#Canceled"), //Cancelado
            C3op_Projects_ProjectStatusConstants::STATUS_SUSPENDED => _("#Suspended"), //Suspenso
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
                    return $this->titles[$type];
                    break;

                default:
                    return _("#Unknown type");
                    break;
            }
    }

    public function AllTitles()
    {
        return $this->titles;
    }
}