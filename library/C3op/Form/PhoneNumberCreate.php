<?php
class C3op_Form_PhoneNumberCreate extends Zend_Form
{
    public function __construct($options = null)
    {
        parent::__construct($options);

        $this->setName('newPhoneNumberForm')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        if (isset($options['id'])) {
            $id = new Zend_Form_Element_Hidden('id');
            $id->addValidator('Int')
                ->addFilter('StringTrim');
            $this->addElement($id);
            $this->setAction('/register/index/change-phone-number');
        }



        $label = new Zend_Form_Element_Text('label');
        $labelValidator = new C3op_Util_ValidString();
        $label->setLabel(_('#Label:'))
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setRequired(true)
            ->addValidator($labelValidator)
            ->addFilter('StringTrim')
                ;
        // attach elements to form
        $this->addElement($label);

        $areaCode = new Zend_Form_Element_Text('areaCode');
        $phoneValidator = new C3op_Register_ValidPhoneAreaCode();
        $areaCode->setLabel(_('#Area code:'))
            ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'one column')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setRequired(false)
            ->addValidator($phoneValidator)
            ->addFilter('StringTrim')
            ;
        $this->addElement($areaCode);

        $localNumber = new Zend_Form_Element_Text('localNumber');
        $phoneValidator = new C3op_Register_ValidPhoneLocalNumber();
        $localNumber->setLabel('Número:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'two columns inset-by-seven omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'one column Right')),
              ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setRequired(false)
            ->addValidator($phoneValidator)
            ->addFilter('StringTrim')
                ;
        // attach elements to form
        $this->addElement($localNumber);

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
            throw new C3op_Form_ContactCreateException('Invalid data!');
        }
        else
        {
            $db = Zend_Registry::get('db');
            $contactMapper = new C3op_Register_ContactMapper($db);
            $contact = $contactMapper->findById($this->contact->GetValue());
            if ($this->localNumber->GetValue() != "") {
                $phoneNumber = new C3op_Register_ContactPhoneNumber(0, $this->areaCode->GetValue(), $this->localNumber->GetValue(), $this->label->GetValue());
                $contact->AddPhoneNumber($phoneNumber);
            }
            $contactMapper->update($contact);
            return $contact->GetId();
        }
    }
}