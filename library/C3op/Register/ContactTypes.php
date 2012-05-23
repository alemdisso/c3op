<?php

class C3op_Register_ContactTypes {
    
    public $titles = array();
    
    public function __construct() {
        $this->titles[C3op_Register_ContactConstants::CONTACT_GENERAL] = "Geral";
        $this->titles[C3op_Register_ContactConstants::CONTACT_CLIENT] = "Cliente";
        $this->titles[C3op_Register_ContactConstants::CONTACT_PARTNER] = "Parceiro";
        $this->titles[C3op_Register_ContactConstants::CONTACT_ASSOCIATE] = "Associado";
        $this->titles[C3op_Register_ContactConstants::CONTACT_CONTRACTOR] = "Contratado";
        $this->titles[C3op_Register_ContactConstants::CONTACT_SUPPLIER] = "Fornecedor";
    }
    
}

?>
