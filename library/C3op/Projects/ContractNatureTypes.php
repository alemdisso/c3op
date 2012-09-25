<?php

class C3op_Projects_ContractNatureTypes {

    private $titles = array();

    public function __construct() {
        $this->titles = array(
            C3op_Projects_ContractNatureConstants::NATURE_CONTRACT => _("#Contract"), //Contrato
            C3op_Projects_ContractNatureConstants::NATURE_AGREEMENT => _("#Agreement"), //Convênio
            C3op_Projects_ContractNatureConstants::NATURE_PARTNERSHIP_TERM => _("#Partnership term"), //Termo de parceria
            C3op_Projects_ContractNatureConstants::NATURE_DONATION => _("#Donation"), //Doação
            C3op_Projects_ContractNatureConstants::NATURE_SPONSORSHIP => _("#Sponsorship"), //Patrocínio
            C3op_Projects_ContractNatureConstants::NATURE_COOPERATION => _("#Cooperation agreement"), //Acordo de cooperação
            C3op_Projects_ContractNatureConstants::NATURE_OTHER => _("#contract.nature.Other"), //Outras
        );
    }

    public function TitleForType($type)
    {
            switch ($type) {
                case C3op_Projects_ContractNatureConstants::NATURE_CONTRACT:
                case C3op_Projects_ContractNatureConstants::NATURE_AGREEMENT:
                case C3op_Projects_ContractNatureConstants::NATURE_PARTNERSHIP_TERM:
                case C3op_Projects_ContractNatureConstants::NATURE_DONATION:
                case C3op_Projects_ContractNatureConstants::NATURE_SPONSORSHIP:
                case C3op_Projects_ContractNatureConstants::NATURE_COOPERATION:
                case C3op_Projects_ContractNatureConstants::NATURE_OTHER:
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