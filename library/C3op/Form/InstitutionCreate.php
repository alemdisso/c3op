<?php
class C3op_Form_InstitutionCreate extends Zend_Form
{
    public function init()
    {

        $this->setName('newInstitutionForm')
            ->setAction('/register/institution/create')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        $element = new Zend_Form_Element_Text('name');
        $validator = new C3op_Register_InstitutionValidName;
        $element->setLabel(_('#Name:'))
              ->addErrorMessage(_("#Name is required"))
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'six columns')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setRequired(true)
            ->addValidator($validator)
            ->addFilter('StringTrim');

        $this->addElement($element);

        $element = new Zend_Form_Element_Text('shortName');
        $validator = new C3op_Register_InstitutionValidName;
        $element->setLabel(_('#Short name:'))
              ->addErrorMessage(_("#Shortname is required"))
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
              ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setRequired(true)
            ->addValidator($validator)
            ->addFilter('StringTrim');

        $this->addElement($element);

        $element = new Zend_Form_Element_Select('type');
        $element->setLabel(_('#Type:'));
        $types = new C3op_Register_InstitutionTypes();
        $titleTypes = $types->AllTitles();
        $element->addMultiOption(null, _("#(choose a type)"))
            ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'four columns')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'Full alpha omega'));

        while (list($key, $title) = each($titleTypes)) {
            $element->addMultiOption($key, $title);
        }
        $this->addElement($element);

        $element = new Zend_Form_Element_Select('relationshipType');
        $element->setLabel(_('#Relationship with us: '));
        $relationshipTypes = new C3op_Register_RelationshipTypes();
        $titleTypes = $relationshipTypes->AllTitles();
        $element->addMultiOption(null, _("#(choose a type)"))
            ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'four columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns Right')),
              ))
            ->setOptions(array('class' => 'Full alpha omega'));

        while (list($key, $title) = each($titleTypes)) {
            $element->addMultiOption($key, $title);
        }

        $this->addElement($element);

        $element = new Zend_Form_Element_Checkbox('legalEntity');
        $element->setLabel(_('#Legal entity:'))
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'one column')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                ))
                ->setOptions(array('checked' => '1', 'unChecked' => '0'))
                ->setValue('0')
                ;
        $this->addElement($element);

        $element = new Zend_Form_Element_Text('registerNumber');
        $element->setLabel('#Register Number:')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns inset-by-five omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
                ))
                ->setOptions(array('class' => 'Full alpha omega'))
            ->addValidator(new C3op_Util_ValidString)
            ->addErrorMessage(_("#Not a valid register number"))
            ->addFilter('StringTrim')
                ;
        $this->addElement($element);

        $element = new Zend_Form_Element_Radio('stateRegistration');
        $element->setLabel('#State Registration: ')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                ))
                ->setMultiOptions(array('1' => _('#institution.registration.state'), '0' => _('#municipal')))
                ->setSeparator('&nbsp;')
                ->setValue('1')
                ;
        $this->addElement($element);

        // $this->addElementText('localRegisterNumber', 'Estadual / Municipal', new C3op_Util_ValidString, 50);

        $element = new Zend_Form_Element_Text('localRegisterNumber');
        $element->setLabel(_('#Local register number:'))
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'two columns inset-by-five omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'one column Right')),
                ))
                ->setOptions(array('class' => 'Full alpha omega'))
            ->addValidator(new C3op_Util_ValidString)
            ->addErrorMessage(_("#Not a valid local register number"))
            ->addFilter('StringTrim')
                ;
        $this->addElement($element);

        $element = new Zend_Form_Element_Text('street');
        $validator = new C3op_Register_InstitutionValidName;
        $element->setLabel('#Street:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'six columns')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setRequired(false)
            ->addErrorMessage(_("#Not a valid street"))
            ->addValidator($validator)
            ->addFilter('StringTrim')
                ;
        $this->addElement($element);

        $element = new Zend_Form_Element_Text('streetNumber');
        $validator = new C3op_Util_ValidString;
        $element->setLabel(_('#Street number'))
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'one column ')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'one column Right')),
              ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setRequired(false)
            ->addValidator($validator)
            ->addFilter('StringTrim')
            ->addErrorMessage(_("#Not a valid street number"))
                ;
        $this->addElement($element);

        $element = new Zend_Form_Element_Text('addressComplement');
        $validator = new C3op_Register_InstitutionValidName;
        $element->setLabel(_('#Address Complement:'))
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'one column omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
              ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setRequired(false)
            ->addValidator($validator)
            ->addFilter('StringTrim')
            ->addErrorMessage(_("#Not a valid register number"))
                ;
        $this->addElement($element);

        $element = new Zend_Form_Element_Text('district');
        $validator = new C3op_Register_InstitutionValidName;
        $element->setLabel(_('#District'))
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'six columns')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setRequired(false)
            ->addValidator($validator)
            ->addFilter('StringTrim')
            ->addErrorMessage(_("#Not a valid district name"))
                ;
        $this->addElement($element);

        $element = new Zend_Form_Element_Text('zipCode');
        $validator = new C3op_Register_ValidZipCode();
        $element->setLabel('#ZIP Code:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
              ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setRequired(false)
            ->addValidator($validator)
            ->addFilter('StringTrim')
            ->addErrorMessage(_("#Not a valid zip code"))
                ;
        $this->addElement($element);

        $element = new Zend_Form_Element_Text('city');
        $validator = new C3op_Register_InstitutionValidName;
        $element->setLabel(_('#City:'))
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eight columns')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setRequired(false)
            ->addValidator($validator)
            ->addFilter('StringTrim')
            ->addErrorMessage(_("#Not a valid city"))
                ;
        $this->addElement($element);


        $element = new Zend_Form_Element_Text('state');
        $validator = new C3op_Register_ValidState();
        $element->setLabel(_('#State:'))
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'one column omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
              ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setRequired(false)
            ->addValidator($validator)
            ->addFilter('StringTrim')
            ->addErrorMessage(_("#Not a valid state"))
                ;
        $this->addElement($element);

        $element = new Zend_Form_Element_Text('website');
        $validator = new C3op_Register_InstitutionValidName;
        $element->setLabel(_('#Website:'))
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setRequired(false)
            ->addValidator($validator)
            ->addFilter('StringTrim')
            ->addErrorMessage(_("#Not a valid website"))
                ;
        $this->addElement($element);

        // create submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit ->setLabel(_('#Submit'))
                ->setDecorators(array('ViewHelper','Errors',
                    array(array('data' => 'HtmlTag'),
                    array('tag' => 'div','class' => 'two columns inset-by-nine omega')),
                    array('Label',
                      array('tag' => 'div','tagClass' => 'three columns alpha Invisible')
                    ),
                  ))
                ->setOptions(array('class' => 'submit Full alpha omega'));
        $this   ->addElement($submit);

    }

    public function process($data) {


        if ($this->isValid($data) !== true)
        {
            print_r($this->getErrorMessages());
            die();
            throw new C3op_Form_InstitutionCreateException(_('#Invalid data!'));
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