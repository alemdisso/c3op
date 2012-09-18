<?php
class C3op_Form_MessengerEdit extends C3op_Form_MessengerCreate
{
    public function __construct($options = null)
    {
        parent::__construct($options);

        $this->setName('newMessengerForm')
            ->setAction('/register/index/change-messenger')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        if (isset($options['id'])) {
            $id = new Zend_Form_Element_Hidden('id');
            $id->addValidator('Int')
                ->addFilter('StringTrim');
            $this->addElement($id);
        } else {
            throw  new C3op_Form_MessengerCreateException(_('#Not defined which messenger to edit.'));
        }
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
                $messengers = $contact->GetMessengers();
                if (isset($messengers[$this->id->GetValue()])) {
                    $messenger = new C3op_Register_ContactMessenger();
                    $messenger->SetId($this->id->GetValue());
                    $messenger->SetAddress($this->address->GetValue());
                    $messenger->SetService($this->service->GetValue());
                    $messengers[$this->id->GetValue()] = $messenger;
                    $contact->SetMessengers($messengers);
                    $contactMapper->update($contact);
                    return $contact->GetId();
                } else {
                    throw new C3op_Form_ContactEditException(_('#Can\'t find this messenger id at this contact messenger list'));
                }
            }
        }
    }
}