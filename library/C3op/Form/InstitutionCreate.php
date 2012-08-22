<?php
class C3op_Form_InstitutionCreate extends Zend_Form
{
    public function init()
    {

        $this->setName('newInstitutionForm')
            ->setAction('/register/institution/create')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        $name = new Zend_Form_Element_Text('name');
        $validator = new C3op_Register_InstitutionValidName;
        $name->setLabel('Nome:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'six columns')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'six columns alpha omega'))
            ->setRequired(true)
            ->addValidator($validator)
//            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim')
                ;
        $this->addElement($name);
        
        $shortName = new Zend_Form_Element_Text('shortName');
        $validator = new C3op_Register_InstitutionValidName;
        $shortName->setLabel('Nome curto:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
              ))
            ->setOptions(array('class' => 'three columns alpha omega'))
            ->setRequired(true)
            ->addValidator($validator)
//            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim')
                ;
        $this->addElement($shortName);

        $type = new Zend_Form_Element_Select('type');
        $type->setLabel('Tipo');
        $titleTypes = C3op_Register_InstitutionTypes::AllTitles();
        $type->addMultiOption(null, "(escolha um tipo)")
            ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'four columns')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'four columns alpha omega'))
        ;
        while (list($key, $title) = each($titleTypes)) {
            $type->addMultiOption($key, $title);
        }        
        $this->addElement($type);

        $relationshipType = new Zend_Form_Element_Select('relationshipType');
        $relationshipType->setLabel('Relação com o IETS: ');
//                ->setRequired(true);
        $titleTypes = C3op_Register_RelationshipTypes::AllTitles();
        $relationshipType->addMultiOption(null, "(escolha um tipo)")
            ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'four columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns Right')),
              ))
            ->setOptions(array('class' => 'four columns alpha omega'))
        ;
        while (list($key, $title) = each($titleTypes)) {
            $relationshipType->addMultiOption($key, $title);
        }                
        $this->addElement($relationshipType);

        $legalEntity = new Zend_Form_Element_Checkbox('legalEntity');
        $legalEntity->setLabel('Pessoa Jurídica: ')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'one column')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                ))
                ->setOptions(array('checked' => '1', 'unChecked' => '0'))
                ->setValue('0')
                ;
        $this->addElement($legalEntity);
        
        
        // $this->addElementText('registerNumber', 'CNPJ / CPF:', new C3op_Util_ValidString, 50);
        
        $elementText = new Zend_Form_Element_Text('registerNumber');
        $elementText->setLabel('CNPJ / CPF:')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns inset-by-five omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
                ))
                ->setOptions(array('class' => 'three columns alpha omega'))
            ->addValidator(new C3op_Util_ValidString)
            ->addFilter('StringTrim')
                ;
        $this->addElement($elementText);
        
        $stateRegistration = new Zend_Form_Element_Radio('stateRegistration');
        $stateRegistration->setLabel('Inscrição: ')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                ))
                ->setMultiOptions(array('1' => 'estadual', '0' => 'municipal'))
                ->setSeparator('&nbsp;')
                ->setValue('1')
                ;
        $this->addElement($stateRegistration);

        // $this->addElementText('localRegisterNumber', 'Estadual / Municipal', new C3op_Util_ValidString, 50);
        
        $elementText = new Zend_Form_Element_Text('localRegisterNumber');
        $elementText->setLabel('número:')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'two columns inset-by-five omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'one column Right')),
                ))
                ->setOptions(array('class' => 'two columns alpha omega'))
            ->addValidator(new C3op_Util_ValidString)
            ->addFilter('StringTrim')
                ;
        $this->addElement($elementText);
        
        // $this->addElementText('street', 'Logradouro:', new C3op_Util_ValidString, 80);

        $name = new Zend_Form_Element_Text('street');
        $validator = new C3op_Register_InstitutionValidName;
        $name->setLabel('Logradouro:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'six columns')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'six columns alpha omega'))
            ->setRequired(false)
            ->addValidator($validator)
//            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim')
                ;
        $this->addElement($name);
        
        // $this->addElementText('streetNumber', 'Número:', new C3op_Util_ValidString, 10);

        $shortName = new Zend_Form_Element_Text('streetNumber');
        $validator = new C3op_Register_InstitutionValidName;
        $shortName->setLabel('Número:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'one column ')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'one column Right')),
              ))
            ->setOptions(array('class' => 'one column alpha omega'))
            ->setRequired(false)
            ->addValidator($validator)
//            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim')
                ;
        $this->addElement($shortName);

        // $this->addElementText('addressComplement', 'Complemento:', new C3op_Util_ValidString, 80);

        $shortName = new Zend_Form_Element_Text('addressComplement');
        $validator = new C3op_Register_InstitutionValidName;
        $shortName->setLabel('Complemento:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'one column omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
              ))
            ->setOptions(array('class' => 'one column alpha omega'))
            ->setRequired(false)
            ->addValidator($validator)
//            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim')
                ;
        $this->addElement($shortName);

        // $this->addElementText('district', 'Bairro:', new C3op_Util_ValidString, 30);

        $name = new Zend_Form_Element_Text('district');
        $validator = new C3op_Register_InstitutionValidName;
        $name->setLabel('Bairro:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'six columns')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'six columns alpha omega'))
            ->setRequired(false)
            ->addValidator($validator)
//            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim')
                ;
        $this->addElement($name);
        
        // $this->addElementText('zipCode', 'CEP:', new C3op_Register_ValidZipCode(), 30);

        $shortName = new Zend_Form_Element_Text('zipCode');
        $validator = new C3op_Register_InstitutionValidName;
        $shortName->setLabel('CEP:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
              ))
            ->setOptions(array('class' => 'three columns alpha omega'))
            ->setRequired(false)
            ->addValidator($validator)
//            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim')
                ;
        $this->addElement($shortName);

        // $this->addElementText('city', 'Cidade:', new C3op_Util_ValidString, 50);

        $name = new Zend_Form_Element_Text('city');
        $validator = new C3op_Register_InstitutionValidName;
        $name->setLabel('Cidade:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eight columns')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'eight columns alpha omega'))
            ->setRequired(false)
            ->addValidator($validator)
//            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim')
                ;
        $this->addElement($name);
        
        // $this->addElementText('state', 'UF:', new C3op_Register_ValidState(), 30);

        $shortName = new Zend_Form_Element_Text('state');
        $validator = new C3op_Register_InstitutionValidName;
        $shortName->setLabel('UF:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'one column omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
              ))
            ->setOptions(array('class' => 'one column alpha omega'))
            ->setRequired(false)
            ->addValidator($validator)
//            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim')
                ;
        $this->addElement($shortName);

        // $this->addElementText('website', 'Website:', new C3op_Util_ValidString, 30);
        
        $name = new Zend_Form_Element_Text('website');
        $validator = new C3op_Register_InstitutionValidName;
        $name->setLabel('Website:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'eight columns alpha omega'))
            ->setRequired(false)
            ->addValidator($validator)
//            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim')
                ;
        $this->addElement($name);
        
        // create submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Gravar')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'five columns inset-by-six omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Invisible')),
              ))
            ->setOptions(array('class' => 'submit two columns alpha omega'));
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