<?php
class C3op_Form_InstitutionEdit extends C3op_Form_InstitutionCreate
{
    public function init()
    {
        parent::init();

        // initialize form
        $this->setName('editInstitutionForm')
            ->setAction('/register/institution/edit')
            ->setMethod('post');
        
        $id = new Zend_Form_Element_Hidden('id');
        $id->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');        
        $this->addElement($id);
        
                        

    }
    
    public function process($data) {
        
        $db = Zend_Registry::get('db');
        $institutionMapper = new C3op_Register_InstitutionMapper($db);        
        
        if ($this->isValid($data) !== true) {
            throw new C3op_Form_InstitutionEditException('Invalid data!');
        } else {
            $id = $data['id'];
            $institution = $institutionMapper->findById($id);
            $institution->SetName($this->name->GetValue());
            $institution->SetShortName($this->shortName->GetValue());
            $institution->SetLegalEntity($this->legalEntity->GetValue());
            $institution->SetRegisterNumber($this->registerNumber->GetValue());
            $institution->SetStateRegistration($this->stateRegistration->GetValue());
            $institution->SetLocalRegisterNumber($this->localRegisterNumber->GetValue());
            $institution->SetStreet($this->street->GetValue());
            $institution->SetStreetNumber($this->streetNumber->GetValue());
            $institution->SetAddressComplement($this->addressComplement->GetValue());
            $institution->SetDistrict($this->district->GetValue());
            $institution->SetZipCode($this->zipCode->GetValue());
            $institution->SetCity($this->city->GetValue());
            $institution->SetState($this->state->GetValue());
            $institution->SetWebsite($this->website->GetValue());
            $institution->SetType($this->type->GetValue());
            $institution->SetRelationshipType($this->relationshipType->GetValue());
            $institutionMapper->update($institution);
        }
    }
 }