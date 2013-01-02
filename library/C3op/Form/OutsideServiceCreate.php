<?php
class C3op_Form_OutsideServiceCreate extends Zend_Form
{

    public function init()
    {
        $this->setName('newOutsideServiceForm')
            ->setAction('/projects/outside-service/create')
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
            ->addErrorMessage(_('#A valid role description is required'))
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

        $element = new Zend_Form_Element_Select('institution', array('onChange' => 'javascript:populateResponsibleAtSupplier()'));
        $element->setLabel('#Institution:')
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
        $element->setLabel('#Who:')
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
            throw new C3op_Form_OutsideServiceCreateException('Invalid data!');
        }
        else
        {
            $db = Zend_Registry::get('db');
            $outsideServiceMapper = new C3op_Projects_OutsideServiceMapper($db);
            $outsideService = new C3op_Projects_OutsideService();
            $outsideService->SetDescription($this->description->GetValue());
            $outsideService->SetInstitution($this->institution->GetValue());
            $outsideService->SetLinkage($this->linkage->GetValue());

            $converter = new C3op_Util_DecimalConverter();
            $validator = new C3op_Util_ValidDecimal();
            if ($validator->isValid($this->value->GetValue())) {
                $outsideService->SetValue($converter->getDecimalDotValue($this->value->GetValue(), $validator));
            }

            $outsideService->SetAction($this->action->GetValue());
            $outsideServiceMapper->insert($outsideService);
            return $outsideService->GetId();
        }
    }


}