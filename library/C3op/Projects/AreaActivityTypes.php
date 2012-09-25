<?php

class C3op_Projects_AreaActivityTypes {

    private $titles = array();


    public function __construct() {
        $this->titles = array(
            C3op_Projects_AreaActivityConstants::AREA_SOCIAL_POLICIES => _("#Social policies and improving public spending"),
            C3op_Projects_AreaActivityConstants::AREA_INEQUALITY => _("#Inequality and poverty"),
            C3op_Projects_AreaActivityConstants::AREA_LABOR_MARKET => _("#Labor market, employment and income"),
            C3op_Projects_AreaActivityConstants::AREA_LOCAL_DEVELOPMENT => _("#Local development and microenterprise"),
            C3op_Projects_AreaActivityConstants::AREA_EDUCATION => _("#Education, human resources and equity"),
            C3op_Projects_AreaActivityConstants::AREA_METROPOLITAN_QUESTION => _("#The metropolitan issue"),
            C3op_Projects_AreaActivityConstants::AREA_CORPORATE_SOCIAL_RESPONSABILITY => _("#Corporate social responsibility"),
        );
    }

    public function TitleForType($type)
    {
            switch ($type) {
                case C3op_Projects_AreaActivityConstants::AREA_SOCIAL_POLICIES:
                case C3op_Projects_AreaActivityConstants::AREA_INEQUALITY:
                case C3op_Projects_AreaActivityConstants::AREA_LABOR_MARKET:
                case C3op_Projects_AreaActivityConstants::AREA_LOCAL_DEVELOPMENT:
                case C3op_Projects_AreaActivityConstants::AREA_EDUCATION:
                case C3op_Projects_AreaActivityConstants::AREA_METROPOLITAN_QUESTION:
                case C3op_Projects_AreaActivityConstants::AREA_CORPORATE_SOCIAL_RESPONSABILITY:
                    return $this->titles[$type];
                    break;

                default:
                    return "Tipo desconhecido";
                    break;
            }
    }

    public function AllTitles()
    {
        return $this->titles;
    }
}