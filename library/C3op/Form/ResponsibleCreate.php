<?php
class C3op_Form_ResponsibleCreate extends Zend_Form
{

    public function init()
    {
        $this->setName('newResponsibleForm')
            //->setAction('/resources/responsible/create')
            ->setAction('javascript:submitResponsibleForm();')
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

            $responsibleMapper = new C3op_Resources_ResponsibleMapper($db);
            $responsible = new C3op_Resources_Responsible();
            if ($type == 'service') {
                $responsible->SetInstitution($this->institution->GetValue());

                $linkageId = $this->linkage->GetValue();
                $linkageMapper = new C3op_Register_LinkageMapper($this->db);
                $linkageContact = $linkageMapper->findById($linkageId);
                $contactId = $linkageContact->GetContact();

                $responsible->SetContact($contactId);

                $converter = new C3op_Util_DecimalConverter();
                $validator = new C3op_Util_ValidDecimal();
                if ($validator->isValid($this->value->GetValue())) {
                    $responsible->SetValue($converter->getDecimalDotValue($this->value->GetValue(), $validator));
                }

                $responsible->SetType(C3op_Resources_ResponsibleTypeConstants::TYPE_OUTSIDE_SERVICE);
                $responsible->SetAction($this->action->GetValue());
                $responsible->SetProject($this->project->GetValue());
                $responsibleMapper->insert($responsible);
                return $responsible->GetId();

            } else {


                $linkageId = $this->linkage->GetValue();
                $linkageMapper = new C3op_Register_LinkageMapper($this->db);
                $linkageContact = $linkageMapper->findById($linkageId);
                $contactId = $linkageContact->GetContact();
                $institutionId = $linkageContact->GetInstitution();
                $responsible->SetInstitution($institutionId);
                $responsible->SetContact($contactId);

                $responsible->SetType(C3op_Resources_ResponsibleTypeConstants::TYPE_TEAM_MEMBER);

                $converter = new C3op_Util_DecimalConverter();
                $validator = new C3op_Util_ValidDecimal();
                if ($validator->isValid($this->value->GetValue())) {
                    $responsible->SetValue($converter->getDecimalDotValue($this->value->GetValue(), $validator));
                }

                $responsible->SetAction($this->action->GetValue());
                $responsible->SetProject($this->project->GetValue());

                $responsibleMapper->insert($responsible);
                return $responsible->GetId();
            }
        }
    }

}