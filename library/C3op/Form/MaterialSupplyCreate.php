<?php
class C3op_Form_MaterialSupplyCreate extends Zend_Form
{

    public function init()
    {
        $this->setName('newMaterialSupplyForm')
            ->setAction('/projects/material-supply/create')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        $action = new Zend_Form_Element_Hidden('action');
        $action->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($action);

        $element = new Zend_Form_Element_Text('description');
        $element->setLabel('#Material:')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'two columns alpha Right')),
                ))
                ->setOptions(array('class' => 'Full alpha omega'))
            ->addValidator(new C3op_Util_ValidString)
            ->setRequired(true)
            ->addErrorMessage(_('#A valid material description is required'))
            ->addFilter('StringTrim')
                ;
        $this->addElement($element);


        $element = new Zend_Form_Element_Text('unitValue');
        $element->setLabel('#Unit value:')
                ->setAttrib('alt','decimal')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'two columns omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'two columns alpha Right')),
                ))
                ->setOptions(array('class' => 'Full alpha omega'))
            ->addValidator(new C3op_Util_ValidPositiveDecimal)
            ->addFilter('StringTrim')
            ->addErrorMessage(_('#The value must be a positive number'))
                ;
        $this->addElement($element);


        $element = new Zend_Form_Element_Text('quantity');
        $element->setLabel('#Quantity:')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'two columns')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'two columns alpha Right')),
                ))
                ->setOptions(array('class' => 'Full alpha omega'))
            ->addValidator(new C3op_Util_ValidString)
            ->setRequired(true)
            ->addErrorMessage(_('#A valid quantity is required'))
            ->addFilter('StringTrim')
                ;
        $this->addElement($element);


        $element = new Zend_Form_Element_Text('unit');
        $validator = new C3op_Util_ValidString();
        $element->setLabel(_('#Unit:'))
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
              ))
                ->setOptions(array('class' => 'Full alpha omega'))
            ->setRequired(false)
            ->addValidator($validator)
            ->addFilter('StringTrim');
        $this->addElement($element);



        $element = new Zend_Form_Element_Select('institution', array('onChange' => 'javascript:populateResponsibleAtSupplier()'));
        $element->setLabel('#Company:')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'two columns alpha Right')),
                ))
                ->setOptions(array('class' => 'Full alpha omega'))
                ->setRegisterInArrayValidator(false);
        $element->addMultiOption(0, _("#undefined"));
        $this->addElement($element);

        $element = new Zend_Form_Element_Select('linkage');
        $element->setLabel('#Responsible at Supplier:')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'two columns alpha Right')),
                ))
                ->setOptions(array('class' => 'Full alpha omega'))
                ->setRegisterInArrayValidator(false);
        $element->addMultiOption(0, _("#undefined"));
        $this->addElement($element);

        // create submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit ->setLabel('#Submit')
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
            throw new C3op_Form_MaterialSupplyCreateException('Invalid data!');
        }
        else
        {
            $db = Zend_Registry::get('db');
            $materialSupplyMapper = new C3op_Projects_MaterialSupplyMapper($db);
            $materialSupply = new C3op_Projects_MaterialSupply();
            $materialSupply->SetDescription($this->description->GetValue());
            $materialSupply->SetInstitution($this->institution->GetValue());
            $materialSupply->SetLinkage($this->linkage->GetValue());
            $materialSupply->SetUnit($this->unit->GetValue());
            if (!$this->institution->GetValue()) {
                $materialSupply->SetStatus(C3op_Projects_MaterialSupplyStatusConstants::STATUS_UNDEFINED);
            }




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
            $materialSupplyMapper->insert($materialSupply);
            return $materialSupply->GetId();
        }
    }


}