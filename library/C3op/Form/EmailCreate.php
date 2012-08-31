<?php
class C3op_Form_EmailCreate extends Zend_Form
{
    public function __construct($options = null)
    {
        parent::__construct($options);

        $this->setName('newEmailForm')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        if (isset($options['id'])) {
            $id = new Zend_Form_Element_Hidden('id');
            $id->addValidator('Int')
                ->addFilter('StringTrim');
            $this->addElement($id);
            $this->setAction('/register/index/change-email');
        }

        $label = new Zend_Form_Element_Text('label');
        $labelValidator = new C3op_Util_ValidString();
        $label->setLabel('Descrição:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'eleven columns alpha omega'))
            ->setRequired(true)
            ->addValidator($labelValidator)
            ->setErrorMessages(array('Esse label está esquisito...'))
            ->addFilter('StringTrim')
                ;
        // attach elements to form
        $this->addElement($label);

        $email = new Zend_Form_Element_Text('email');
        $emailValidator = new C3op_Util_ValidEmail();
        $email->setLabel('Email:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'eleven columns alpha omega'))
            ->setRequired(false)
            ->addValidator($emailValidator)
            ->addFilter('StringTrim')
                ;
        // attach elements to form
        $this->addElement($email);

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
            throw new C3op_Form_ContactCreateException('Invalid data!');
        }
        else
        {
            $db = Zend_Registry::get('db');
            $contactMapper = new C3op_Register_ContactMapper($db);
            $contact = $contactMapper->findById($this->contact->GetValue());
            if ($this->localNumber->GetValue() != "") {
                $email = new C3op_Register_ContactEmail(0, $this->areaCode->GetValue(), $this->localNumber->GetValue(), $this->label->GetValue());
                $contact->AddEmail($email);
            }
            $contactMapper->update($contact);
            return $contact->GetId();
        }
    }
}