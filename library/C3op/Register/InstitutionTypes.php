<?php

class C3op_Register_InstitutionTypes {

    private $titles = array();

    public function __construct() {
        $this->titles = array(
            C3op_Register_InstitutionConstants::INSTITUTION_PUBLIC             => _("#Government"),
            C3op_Register_InstitutionConstants::INSTITUTION_PRIVATE            => _("#Private Entity"),
            C3op_Register_InstitutionConstants::INSTITUTION_RESEARCH           => _("#Research Institute"),
            C3op_Register_InstitutionConstants::INSTITUTION_NGO                => _("#Non Governmental Organization"),
            C3op_Register_InstitutionConstants::INSTITUTION_PERSONAL           => _("#Particular"),
            C3op_Register_InstitutionConstants::INSTITUTION_PRIVATE_FOUNDATION => _("#Private Foundation"),
            C3op_Register_InstitutionConstants::INSTITUTION_PUBLIC_FOUNDATION  => _("#Public Foundation"),
        );

    }

    public function TitleForType($type)
    {
            switch ($type) {
                case C3op_Register_InstitutionConstants::INSTITUTION_PUBLIC:
                case C3op_Register_InstitutionConstants::INSTITUTION_PRIVATE:
                case C3op_Register_InstitutionConstants::INSTITUTION_RESEARCH:
                case C3op_Register_InstitutionConstants::INSTITUTION_NGO:
                case C3op_Register_InstitutionConstants::INSTITUTION_PERSONAL:
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