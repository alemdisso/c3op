<?php
class C3op_Form_ContractEdit extends C3op_Form_ContractCreate
{
    public function init()
    {
        parent::init();

        // initialize form
        $this->setName('editContractForm')
            ->setAction('/projects/contract/edit')
            ->setMethod('post');

        $id = new Zend_Form_Element_Hidden('id');
        $id->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($id);



    }

    public function process($data) {

        $db = Zend_Registry::get('db');
        $contractMapper = new C3op_Projects_ContractMapper($db);

        if ($this->isValid($data) !== true) {
            throw new C3op_Form_ContractEditException('Invalid data!');
        } else {

            $id = $data['id'];
            $contract = $contractMapper->findById($id);
            $contract->SetTitle($data['title']);
            $contract->SetProject($data['project']);
            $contract->SetSigningDate($this->prepareDateValueToSet($data['signingDate'], new C3op_Util_ValidDate(), new C3op_Util_DateConverter()));
            $contract->SetAmendment($data['amendment']);
            $contractMapper->update($contract);
            return $id;
        }
    }

    private function prepareDateValueToSet($value, C3op_Util_ValidDate $validator, C3op_Util_DateConverter $converter)
    {
        if ($validator->isValid($value)) {
            return $converter->convertDateToMySQLFormat($value);
        } else {
            return "";
        }
    }

}
