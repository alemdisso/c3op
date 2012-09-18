<?php
class C3op_Form_MessengerCreate extends Zend_Form
{
    public function __construct($options = null)
    {
        parent::__construct($options);

        $this->setName('newMessengerForm')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        if (isset($options['id'])) {
            $id = new Zend_Form_Element_Hidden('id');
            $id->addValidator('Int')
                ->addFilter('StringTrim');
            $this->addElement($id);
            $this->setAction('/register/index/change-messenger');
        }

        $service = new Zend_Form_Element_Text('service');
        $serviceValidator = new C3op_Util_ValidString();
        $service->setLabel(_('#Service:'))
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'eleven columns alpha omega'))
            ->setRequired(true)
            ->addValidator($serviceValidator)
            ->setErrorMessages(array(_('#Invalid service')))
            ->addFilter('StringTrim')
                ;
        // attach elements to form
        $this->addElement($service);

        $address = new Zend_Form_Element_Text('address');
        $messengerValidator = new C3op_Util_ValidString();
        $address->setLabel(_('#messenger.Address:'))
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'eleven columns alpha omega'))
            ->setRequired(false)
            ->addValidator($messengerValidator)
            ->setErrorMessages(array(_('#Invalid address')))
            ->addFilter('StringTrim')
                ;
        // attach elements to form
        $this->addElement($address);

        // create submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel(_('#Submit'))
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
            throw new C3op_Form_ContactCreateException(_('#Invalid data!'));
        }
        else
        {
            $db = Zend_Registry::get('db');
            $contactMapper = new C3op_Register_ContactMapper($db);
            $contact = $contactMapper->findById($this->contact->GetValue());
            if ($this->localNumber->GetValue() != "") {
                $messenger = new C3op_Register_ContactMessenger(0, $this->areaCode->GetValue(), $this->localNumber->GetValue(), $this->service->GetValue());
                $contact->AddMessenger($messenger);
            }
            $contactMapper->update($contact);
            return $contact->GetId();
        }
    }
}