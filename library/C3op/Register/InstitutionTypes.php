<?php

class C3op_Register_InstitutionTypes {

    private $titles = array();

    public function __construct() {
        $this->titles = array(
                C3op_Register_InstitutionConstants::INSTITUTION_PUBLIC => _("#Government"), //Poder público
                C3op_Register_InstitutionConstants::INSTITUTION_PRIVATE => _("#Private Entity"), //Entidade Privada
                C3op_Register_InstitutionConstants::INSTITUTION_RESEARCH => _("#Research Institute"), //Instituto de pesquisa
                C3op_Register_InstitutionConstants::INSTITUTION_NGO => _("#Non Governmental Organization"), //Organização Não Governamental
                C3op_Register_InstitutionConstants::INSTITUTION_PERSONAL => _("#Particular"), //Particular
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
                    return "Tipo desconhecido";
                    break;
            }
    }

    public function AllTitles()
    {
        return $this->titles;
    }
}