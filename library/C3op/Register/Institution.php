<?php

class C3op_Register_Institution
{
	
    protected $id;
    protected $name;
    protected $shortName;
    protected $legalEntity;
    protected $registerNumber;
    protected $stateRegistration;
    protected $localRegisterNumber;
    protected $street;
    protected $streetNumber;
    protected $addressComplement;
    protected $zipCode;
    protected $district;
    protected $city;
    protected $state;
    protected $website;
    protected $type;
    protected $relationshipType;

    function __construct($id=0) {
        $this->id = (int)$id;
        $this->name = "";
        $this->shortName = "";
        $this->legalEntity = true;
        $this->registerNumber = "";
        $this->stateRegistration = "";
        $this->localRegisterNumber = "";
        $this->street = "";
        $this->streetNumber = "";
        $this->addressComplement = "";
        $this->zipCode = "";
        $this->district = "";
        $this->city = "";
        $this->state = "";
        $this->website = "";
        $this->type = "";
        $this->relationshipType = "";
    }

    public function GetId() {
        return $this->id;
    } //GetId

    public function SetId($id) {
        if (($this->id == 0) && ($id > 0)) {
            $this->id = (int)$id;
        } else {
            throw new C3op_Register_InstitutionException('It\'s not possible to change a Institution\'s ID');
        }
    } //SetId

    public function GetName() {
        return $this->name;
    } //GetName
	
    public function SetName($name) {
        $validator = new C3op_Register_InstitutionValidName();
        if ($validator->isValid($name)) {
            if ($this->name != $name) {
                $this->name = $name;
            }
        } else {
            throw new C3op_Register_InstitutionException("This ($name) is not a valid name.");
        }

    } //SetName

    public function GetShortName() {
        return $this->shortName;
    } //GetShortName
	
    public function SetShortName($shortName) {
        $validator = new C3op_Register_InstitutionValidName();
        if (($validator->isValid($shortName)) && (strlen($shortName) <= 40)) {
            if ($this->shortName != $shortName) {
                $this->shortName = $shortName;
            }
        } else {
            throw new C3op_Register_InstitutionException("This ($shortName) is not a valid shortName.");
        }

    } //SetShortName

    public function GetLegalEntity() {
        return $this->legalEntity;
    } //GetLegalEntity
	
    public function SetLegalEntity($legalEntity) {
        if ($legalEntity == 1) {
            $this->legalEntity = $legalEntity;
        } else {
            $this->legalEntity = 0;
        }
    } //SetLegalEntity

    public function GetRegisterNumber() {
        return $this->registerNumber;
    } //GetRegisterNumber
	
    public function SetRegisterNumber($registerNumber) {
        $validator = new C3op_Util_ValidString();
        if ($validator->isValid($registerNumber)) {
            if ($this->registerNumber != $registerNumber) {
                $this->registerNumber = $registerNumber;
            }
        } else {
            throw new C3op_Register_InstitutionException("This ($registerNumber) is not a valid Register Number.");
        }

    } //SetRegisterNumber

    public function GetStateRegistration() {
        return $this->stateRegistration;
    } //GetStateRegistration
	
    public function SetStateRegistration($stateRegistration) {
        if ($stateRegistration) {
            $this->stateRegistration = $stateRegistration;
        } else {
            $this->stateRegistration = 0;
        }
    } //SetStateRegistration

    public function GetLocalRegisterNumber() {
        return $this->localRegisterNumber;
    } //GetLocalRegisterNumber
	
    public function SetLocalRegisterNumber($localRegisterNumber) {
        $validator = new C3op_Util_ValidString();
        if ($validator->isValid($localRegisterNumber)) {
            if ($this->localRegisterNumber != $localRegisterNumber) {
                $this->localRegisterNumber = $localRegisterNumber;
            }
        } else {
            throw new C3op_Register_InstitutionException("This ($localRegisterNumber) is not a valid Local Register Number.");
        }

    } //SetLocalRegisterNumber

    public function GetStreet() {
        return $this->street;
    } //GetStreet
	
    public function SetStreet($street) {
        $validator = new C3op_Util_ValidString();
        if ($validator->isValid($street)) {
            if ($this->street != $street) {
                $this->street = $street;
            }
        } else {
            throw new C3op_Register_InstitutionException("This ($street) is not a valid street.");
        }

    } //SetStreet

    public function GetStreetNumber() {
        return $this->streetNumber;
    } //GetStreetNumber
	
    public function SetStreetNumber($streetNumber) {
        $validator = new C3op_Util_ValidString();
        if ($validator->isValid($streetNumber)) {
            if ($this->streetNumber != $streetNumber) {
                $this->streetNumber = $streetNumber;
            }
        } else {
            throw new C3op_Register_InstitutionException("This ($streetNumber) is not a valid Street Number.");
        }

    } //SetStreetNumber

    public function GetAddressComplement() {
        return $this->addressComplement;
    } //GetAddressComplement
	
    public function SetAddressComplement($addressComplement) {
        $validator = new C3op_Util_ValidString();
        if ($validator->isValid($addressComplement)) {
            if ($this->addressComplement != $addressComplement) {
                $this->addressComplement = $addressComplement;
            }
        } else {
            throw new C3op_Register_InstitutionException("This ($addressComplement) is not a valid Address Complement.");
        }

    } //SetAddressComplement

    public function GetZipCode() {
        return $this->zipCode;
    } //GetZipCode
	
    public function SetZipCode($zipCode) {
        $validator = new C3op_Register_ValidZipCode();
        if ($validator->isValid($zipCode)) {
            if ($this->zipCode != $zipCode) {
                $this->zipCode = $zipCode;
            }
        } else {
            throw new C3op_Register_InstitutionException("This ($zipCode) is not a valid zipCode.");
        }

    } //SetZipCode

    public function GetDistrict() {
        return $this->district;
    } //GetDistrict
	
    public function SetDistrict($district) {
        $validator = new C3op_Util_ValidString();
        if ($validator->isValid($district)) {
            if ($this->district != $district) {
                $this->district = $district;
            }
        } else {
            throw new C3op_Register_InstitutionException("This ($district) is not a valid district.");
        }

    } //SetDistrict

    public function GetCity() {
        return $this->city;
    } //GetCity
	
    public function SetCity($city) {
        $validator = new C3op_Util_ValidString();
        if ($validator->isValid($city)) {
            if ($this->city != $city) {
                $this->city = $city;
            }
        } else {
            throw new C3op_Register_InstitutionException("This ($city) is not a valid city.");
        }

    } //SetCity

    public function GetState() {
        return $this->state;
    } //GetState
	
    public function SetState($state) {
        $validator = new C3op_Register_ValidState();
        if ($validator->isValid($state)) {
            if ($this->state != $state) {
                $this->state = $state;
            }
        } else {
            throw new C3op_Register_InstitutionException("This ($state) is not a valid state.");
        }

    } //SetState

    public function GetWebsite() {
        return $this->website;
    } //GetWebsite
	
    public function SetWebsite($website) {
        $validator = new C3op_Util_ValidString();
        if ($validator->isValid($website)) {
            if ($this->website != $website) {
                $this->website = $website;
            }
        } else {
            throw new C3op_Register_InstitutionException("This ($website) is not a valid website.");
        }

    } //SetWebsite

    public function GetType() 
    {
        return $this->type;
    }
    
    public function SetType($type) 
    {
        switch ($type) {
            case C3op_Register_InstitutionConstants::INSTITUTION_PUBLIC:
            case C3op_Register_InstitutionConstants::INSTITUTION_PRIVATE:
            case C3op_Register_InstitutionConstants::INSTITUTION_RESEARCH:
            case C3op_Register_InstitutionConstants::INSTITUTION_NGO:
            case C3op_Register_InstitutionConstants::INSTITUTION_PERSONAL:
                $this->type = (int) $type;
                break;
            
            case null:
            case "":
            case 0:
            case false:
                $this->type = null;
                break;
                 
            default:
                throw new C3op_Register_InstitutionException("Invalid type.");
                break;
        }
    }

    public function GetRelationshipType() 
    {
        return $this->relationshipType;
    }
    
    public function SetRelationshipType($relationShipType) 
    {
        switch ($relationShipType) {
            case C3op_Register_InstitutionRelationshipConstants::RELATIONSHIP_GENERAL:
            case C3op_Register_InstitutionRelationshipConstants::RELATIONSHIP_CLIENT:
            case C3op_Register_InstitutionRelationshipConstants::RELATIONSHIP_PARTNER:
            case C3op_Register_InstitutionRelationshipConstants::RELATIONSHIP_SUPPLIER:
            case C3op_Register_InstitutionRelationshipConstants::RELATIONSHIP_CONTRACTING:
                $this->relationshipType = (int) $relationShipType;
                break;
            
            case null:
            case "":
            case 0:
            case false:
                $this->relationshipType = null;
                break;
                 
            default:
                throw new C3op_Register_InstitutionException("Invalid relationshiptype.");
                break;
        }
    }

}