<?php
class C3op_Form_ContactCreate extends Zend_Form
{
    public function init()
    {

        // initialize form
        $this->setName('newContactForm')
            ->setAction('/register/contact/create')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        $element = new Zend_Form_Element_Text('name');
        $nameValidator = new C3op_Register_ContactValidName();
        $element->setLabel('#Name:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setRequired(true)
            ->addErrorMessage(_("#Invalid name"))
            ->addValidator($nameValidator)
            ->addFilter('StringTrim');
        $this->addElement($element);

        $element = new Zend_Form_Element_Text('areaCode');
        $phoneValidator = new C3op_Register_ValidPhoneAreaCode();
        $element->setLabel(_('#Area code:'))
                 ->setAttrib('alt','ddd')
                 ->setDecorators(array(
                   'ViewHelper',
                   'Errors',
                   array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'one column')),
                   array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                 ))
                 ->setOptions(array('class' => 'Full alpha omega'))
                 ->setRequired(false)
                 ->addValidator($phoneValidator)
                 ->addFilter('StringTrim');
        $this->addElement($element);

        $element = new Zend_Form_Element_Text('localNumber');
        $phoneValidator = new C3op_Register_ValidPhoneLocalNumber();
        $element->setLabel(_('#Local number:'))
                 ->setAttrib('alt','tel')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'two columns')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'one column Right')),
              ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setRequired(false)
            ->addValidator($phoneValidator)
            ->addFilter('StringTrim');
        $this->addElement($element);

        $element = new Zend_Form_Element_Select('type');
        $element->setLabel(_('#Relationship with us:'))
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'five columns omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
                ))
                ->setOptions(array('class' => 'Full alpha omega'))
                ->setRequired(true);
        $titleTypes = C3op_Register_ContactTypes::AllTitles();
        $element->addMultiOption(null, _("#(click to choice)"));
        while (list($key, $title) = each($titleTypes)) {
            $element->addMultiOption($key, $title);
        }
        $this->addElement($element);

        // create submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel(_('#Submit'))
               ->setDecorators(array('ViewHelper','Errors',
                    array(array('data' => 'HtmlTag'),
                    array('tag' => 'div','class' => 'two columns inset-by-nine omega')),
                    array('Label',
                      array('tag' => 'div','tagClass' => 'three columns alpha Invisible')
                    ),
                  ))
               ->setOptions(array('class' => 'submit Full alpha omega'));
        $this->addElement($submit);

    }

    public function process($data) {
        if ($this->isValid($data) !== true)
        {
            throw new C3op_Form_ContactCreateException(_('#Invalid data!'));
        }
        else
        {
            $db = Zend_Registry::get('db');
            $contactMapper = new C3op_Register_ContactMapper($db);
            $contact = new C3op_Register_Contact();
            $contact->SetName($this->name->GetValue());
            $contact->SetType($this->type->GetValue());
            if ($this->localNumber->GetValue() != "") {
                $phoneNumber = new C3op_Register_ContactPhoneNumber(0, $this->areaCode->GetValue(), $this->localNumber->GetValue(), '');
                $contact->AddPhoneNumber($phoneNumber);
            }
            $contactMapper->insert($contact);
            return $contact->getId();
        }
    }
}