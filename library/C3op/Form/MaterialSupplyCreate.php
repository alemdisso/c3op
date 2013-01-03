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
        $element->setLabel('#Service:')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'seven columns')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                ))
                ->setOptions(array('class' => 'Full alpha omega'))
            ->addValidator(new C3op_Util_ValidString)
            ->setRequired(true)
            ->addErrorMessage(_('#A valid material description is required'))
            ->addFilter('StringTrim')
                ;
        $this->addElement($element);


        $element = new Zend_Form_Element_Text('value');
        $element->setLabel('#Value:')
                ->setAttrib('alt','decimal')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'one column Right')),
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
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'five columns')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                ))
                ->setOptions(array('class' => 'Full alpha omega'))
            ->addValidator(new C3op_Util_ValidString)
            ->setRequired(true)
            ->addErrorMessage(_('#A valid quantity is required'))
            ->addFilter('StringTrim')
                ;
        $this->addElement($element);




        $element = new Zend_Form_Element_Select('institution', array('onChange' => 'javascript:populateResponsibleAtSupplier()'));
        $element->setLabel('#Company:')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
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
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
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

            $converter = new C3op_Util_DecimalConverter();
            $validator = new C3op_Util_ValidDecimal();
            if ($validator->isValid($this->value->GetValue())) {
                $materialSupply->SetValue($converter->getDecimalDotValue($this->value->GetValue(), $validator));
            }

            $materialSupply->SetAction($this->action->GetValue());
            $materialSupplyMapper->insert($materialSupply);
            return $materialSupply->GetId();
        }
    }


}