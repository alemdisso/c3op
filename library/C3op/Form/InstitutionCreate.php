<?php
class C3op_Form_InstitutionCreate extends Zend_Form
{
    public function init()
    {

        $this->setName('newInstitutionForm')
            ->setAction('/register/institution/create')
            ->setMethod('post');

        $name = new Zend_Form_Element_Text('name');
        $validator = new C3op_Register_InstitutionValidName;
        $name->setLabel('Nome:')
            ->setOptions(array('size' => '50'))
            ->setRequired(true)
            ->addValidator($validator)
//            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim')
                ;
        $this->addElement($name);
        
        $shortName = new Zend_Form_Element_Text('shortName');
        $validator = new C3op_Register_InstitutionValidName;
        $shortName->setLabel('Nome curto:')
            ->setOptions(array('size' => '50'))
            ->setRequired(true)
            ->addValidator($validator)
//            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim')
                ;
        $this->addElement($shortName);
        $legalEntity = new Zend_Form_Element_Checkbox('legalEntity');
        $legalEntity->setLabel('Pessoa Jurídica: ')
                ->addDecorator('Label', array('placement' => 'PREPEND')) 
                ->setOptions(array('checked' => '1', 'unChecked' => '0'))
                ->setValue('0')
                ;
        $this->addElement($legalEntity);
        
        
        $this->addElementText('registerNumber', 'CNPJ / CPF:', new C3op_Util_ValidString, 50);
        
        $stateRegistration = new Zend_Form_Element_Radio('stateRegistration');
        $stateRegistration->setLabel('Inscrição: ')
                ->addDecorator('Label', array('placement' => 'PREPEND')) 
                ->setMultiOptions(array('1' => 'Estadual', '0' => 'Municipal'))
                ->setValue('1')
                ;
        $this->addElement($stateRegistration);
        $this->addElementText('localRegisterNumber', 'Estadual / Municipal', new C3op_Util_ValidString, 50);
        $this->addElementText('street', 'Logradouro:', new C3op_Util_ValidString, 80);
        $this->addElementText('streetNumber', 'Número:', new C3op_Util_ValidString, 10);
        $this->addElementText('addressComplement', 'Complemento:', new C3op_Util_ValidString, 80);
        $this->addElementText('district', 'Bairro:', new C3op_Util_ValidString, 30);
        $this->addElementText('zipCode', 'CEP:', new C3op_Register_ValidZipCode(), 30);
        $this->addElementText('city', 'Cidade:', new C3op_Util_ValidString, 50);
        $this->addElementText('state', 'UF:', new C3op_Register_ValidState(), 30);
        $this->addElementText('website', 'Website:', new C3op_Util_ValidString, 30);
        
        $type = new Zend_Form_Element_Select('type');
        $type->setLabel('Tipo');
        $titleTypes = C3op_Register_InstitutionTypes::AllTitles();
        $type->addMultiOption(null, "(escolha um tipo)");
        while (list($key, $title) = each($titleTypes)) {
            $type->addMultiOption($key, $title);
        }        
        $this->addElement($type);

        $relationshipType = new Zend_Form_Element_Select('relationshipType');
        $relationshipType->setLabel('Relação com o IETS: ');
//                ->setRequired(true);
        $titleTypes = C3op_Register_RelationshipTypes::AllTitles();
        $relationshipType->addMultiOption(null, "(escolha um tipo)");
        while (list($key, $title) = each($titleTypes)) {
            $relationshipType->addMultiOption($key, $title);
        }                
        $this->addElement($relationshipType);

        // create submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Salvar')
            ->setOptions(array('class' => 'submit'));
        $this->addElement($submit);
                

    }
    
    public function process($data) {
        
        
        if ($this->isValid($data) !== true) 
        {
            print_r($this->getErrorMessages());
            die();
            throw new C3op_Form_InstitutionCreateException('Invalid data!');
        } 
        else
        {
            $db = Zend_Registry::get('db');
            $institutionMapper = new C3op_Register_InstitutionMapper($db);
            
            $institution = new C3op_Register_Institution();
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
            
            
            $institutionMapper->insert($institution);
        }
    }
    
    private function addElementText($fieldName, $label, $validator, $fieldSize)
    {
        $elementText = new Zend_Form_Element_Text($fieldName);
        $elementText->setLabel($label)
            ->setOptions(array('size' => "$fieldSize"))
            ->addValidator($validator)
            ->addFilter('StringTrim')
                ;
        $this->addElement($elementText);
        
    }
    
 }