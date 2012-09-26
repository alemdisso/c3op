<?php

class C3op_Register_RelationshipTypes {

    private $titles = array();

    public function __construct() {
        $this->titles = array(
            C3op_Register_InstitutionConstants::RELATIONSHIP_GENERAL => _("#General"),  //Geral
            C3op_Register_InstitutionConstants::RELATIONSHIP_CLIENT => _("#Client"), //Cliente
            C3op_Register_InstitutionConstants::RELATIONSHIP_PARTNER => _("#Partner"), //Parceiro
            C3op_Register_InstitutionConstants::RELATIONSHIP_SUPPLIER => _("#Supplier"), //Fornecedor
            C3op_Register_InstitutionConstants::RELATIONSHIP_CONTRACTING => _("#Contractor"), //COntratante
        );

    }

    public function TitleForType($type)
    {
            switch ($type) {
                case C3op_Register_InstitutionConstants::RELATIONSHIP_GENERAL:
                case C3op_Register_InstitutionConstants::RELATIONSHIP_CLIENT:
                case C3op_Register_InstitutionConstants::RELATIONSHIP_PARTNER:
                case C3op_Register_InstitutionConstants::RELATIONSHIP_SUPPLIER:
                case C3op_Register_InstitutionConstants::RELATIONSHIP_CONTRACTING:
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