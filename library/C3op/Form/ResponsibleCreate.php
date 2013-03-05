<?php
class C3op_Form_ResponsibleCreate extends Zend_Form
{

    public function init()
    {
        $this->setName('newResponsibleForm')
            ->setAction('/resources/responsible/create')
            //->setAction('javascript:submitResponsibleForm();')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        $element = new Zend_Form_Element_Hidden('action');
        $element->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($element);
        $element->setDecorators(array('ViewHelper'));

        $element = new Zend_Form_Element_Hidden('project');
        $element->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($element);
        $element->setDecorators(array('ViewHelper'));

        $element = new Zend_Form_Element_Radio('responsibleType');
	$element->setRequired(true)
		->setLabel('#It is a person or an institution?')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'six columns omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'five column alpha Right')),
                ))
		->setMultiOptions(array("teamMember" => _('#teamMember'), "service" => _('#service')));
        $this->addElement($element);

        $element = new Zend_Form_Element_Select('institution', array('onChange' => 'javascript:populateResponsibleAtSupplier()'));
        $element->setLabel('#Company:')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'six columns omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'two columns alpha Right')),
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
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'six columns alpha omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'two columns alpha Right')),
                ))
                ->setOptions(array('class' => 'Full alpha omega'))
                ->setRegisterInArrayValidator(false);
        $element->addMultiOption(0, _("#undefined"));
        $this->addElement($element);

        $element = new Zend_Form_Element_Text('value');
        $element->setLabel('#Value:')
                ->setAttrib('alt','decimal')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns alpha omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'two columns alpha Right inset-by-two')),
                ))
                ->setOptions(array('class' => 'Full alpha omega'))
            ->addValidator(new C3op_Util_ValidPositiveDecimal)
            ->addFilter('StringTrim')
            ->addErrorMessage(_('#The value must be a positive number'))
                ;
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
            throw new C3op_Form_TeamMemberCreateException('Invalid data!');
        }
        else
        {
            $db = Zend_Registry::get('db');
            $type = $this->responsibleType->getValue();

            if ($type == 'service') {
                $outsideServiceMapper = new C3op_Resources_OutsideServiceMapper($db);
                $outsideService = new C3op_Resources_OutsideService();
                $outsideService->SetDescription("?!?action description?!?");
                $outsideService->SetInstitution($this->institution->GetValue());
                $outsideService->SetLinkage($this->linkage->GetValue());

                $converter = new C3op_Util_DecimalConverter();
                $validator = new C3op_Util_ValidDecimal();
                if ($validator->isValid($this->value->GetValue())) {
                    $outsideService->SetValue($converter->getDecimalDotValue($this->value->GetValue(), $validator));
                }

                $outsideService->SetAction($this->action->GetValue());
                $outsideService->SetProject($this->project->GetValue());
                $outsideServiceMapper->insert($outsideService);
                return $outsideService->GetAction();

            } else {


                $teamMemberMapper = new C3op_Resources_TeamMemberMapper($db);
                $teamMember = new C3op_Resources_TeamMember();
                $teamMember->SetDescription("?!?action description?!?");
                $teamMember->SetLinkage($this->linkage->GetValue());

                $converter = new C3op_Util_DecimalConverter();
                $validator = new C3op_Util_ValidDecimal();
                if ($validator->isValid($this->value->GetValue())) {
                    $teamMember->SetValue($converter->getDecimalDotValue($this->value->GetValue(), $validator));
                }

                $teamMember->SetAction($this->action->GetValue());
                $teamMember->SetProject($this->project->GetValue());
                $teamMemberMapper->insert($teamMember);
                return $teamMember->GetAction();
            }
        }
    }

}