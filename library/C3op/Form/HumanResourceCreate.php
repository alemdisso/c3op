<?php
class C3op_Form_HumanResourceCreate extends Zend_Form
{

    public function init()
    {
        $this->setName('newHumanResourceForm')
            ->setAction('/projects/human-resource/create')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        $action = new Zend_Form_Element_Hidden('action');
        $action->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($action);

        // $this->addElementText('description', 'Atributo:', new C3op_Util_ValidString(), 50);

        $element = new Zend_Form_Element_Text('description');
        $element->setLabel('Atributo:')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                ))
                ->setOptions(array('class' => 'Full alpha omega'))
            ->addValidator(new C3op_Util_ValidString)
            ->addFilter('StringTrim')
                ;
        $this->addElement($element);

        // $this->addElementText('value', 'Valor:', new C3op_Util_ValidPositiveFloat(), 50);

        $element = new Zend_Form_Element_Text('value');
        $element->setLabel('Valor:')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                ))
                ->setOptions(array('class' => 'Full alpha omega'))
            ->addValidator(new C3op_Util_ValidString)
            ->addFilter('StringTrim')
                ;
        $this->addElement($element);

        $element = new Zend_Form_Element_Select('contact');
        $element->setLabel('Quem: ')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                ))
                ->setOptions(array('class' => 'Full alpha omega'))
                ->setRegisterInArrayValidator(false);
        $element->addMultiOption(0, "indefinido");
        $this->addElement($element);

        // create submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit ->setLabel('Gravar')
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
            throw new C3op_Form_HumanResourceCreateException('Invalid data!');
        }
        else
        {
            $db = Zend_Registry::get('db');
            $humanResourceMapper = new C3op_Projects_HumanResourceMapper($db);
            $humanResource = new C3op_Projects_HumanResource();
            $humanResource->SetDescription($this->description->GetValue());
            $humanResource->SetContact($this->contact->GetValue());
            $humanResource->SetValue($this->value->GetValue());
            $humanResource->SetAction($this->action->GetValue());
            $humanResourceMapper->insert($humanResource);
            return $humanResource->GetId();
        }
    }

    private function addElementText($fieldName, $label, $validator, $fieldSize)
    {
        $element = new Zend_Form_Element_Text($fieldName);
        $element->setLabel($label)
            ->setOptions(array('size' => "$fieldSize"))
            ->addValidator($validator)
            ->addFilter('StringTrim')
                ;
        $this->addElement($element);

    }


}