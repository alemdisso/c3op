<?php
class C3op_Form_EmailEdit extends C3op_Form_EmailCreate
{
    public function __construct($options = null)
    {
        parent::__construct($options);

        $this->setName('newEmailForm')
            ->setAction('/register/index/change-email')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        if (isset($options['id'])) {
            $id = new Zend_Form_Element_Hidden('id');
            $id->addValidator('Int')
                ->addFilter('StringTrim');
            $this->addElement($id);
        } else {
            throw  new C3op_Form_EmailCreateException('Not defined which email to edit.');
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
                $emails = $contact->GetEmails();
                if (isset($emails[$this->id->GetValue()])) {
                    $email = new C3op_Register_ContactEmail();
                    $email->SetId($this->id->GetValue());
                    $email->SetEmail($this->email->GetValue());
                    $email->SetLabel($this->label->GetValue());
                    $emails[$this->id->GetValue()] = $email;
                    $contact->SetEmails($emails);
                    $contactMapper->update($contact);
                    return $contact->GetId();

                } else {
                    throw new C3op_Form_ContactEditException('Can\'t find this email id at this contact email list');
                }
            }


        }
    }
}