<?php
class C3op_Form_MaterialSupplyEdit extends C3op_Form_MaterialSupplyCreate
{
    public function init()
    {
        parent::init();

        // initialize form
        $this->setName('editMaterialSupplyForm')
            ->setAction('/resources/material-supply/edit')
            ->setMethod('post');

        $id = new Zend_Form_Element_Hidden('id');
        $id->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($id);



    }

    public function process($data) {

        $db = Zend_Registry::get('db');
        $materialSupplyMapper = new C3op_Resources_MaterialSupplyMapper($db);

        if ($this->isValid($data) !== true) {
            throw new C3op_Form_InstitutionEditException('Invalid data!');
        } else {
            $id = $data['id'];
            $materialSupply = $materialSupplyMapper->findById($id);
            $materialSupply->SetDescription($this->description->GetValue());
            $materialSupply->SetInstitution($this->institution->GetValue());
            $materialSupply->SetLinkage($this->linkage->GetValue());
            $materialSupply->SetUnit($this->unit->GetValue());

            $converter = new C3op_Util_DecimalConverter();
            $validator = new C3op_Util_ValidDecimal();
            if ($validator->isValid($this->unitValue->GetValue())) {
                $materialSupply->SetUnitValue($converter->getDecimalDotValue($this->unitValue->GetValue(), $validator));
            }
            $validator = new C3op_Util_ValidPositiveInteger();
            if ($validator->isValid($this->quantity->GetValue())) {
                $materialSupply->setQuantity($this->quantity->GetValue());
            }

            $materialSupply->SetAction($this->action->GetValue());
            $materialSupplyMapper->update($materialSupply);
            return $materialSupply->GetId();
        }
    }
 }