<?php
class C3op_Form_ContactEdit extends C3op_Form_ContactCreate
{
    public function init()
    {
        parent::init();

        // initialize form
        $this->setName('editContactForm')
            ->setAction('/register/contact/edit')
            ->setMethod('post');

        $element = new Zend_Form_Element_Hidden('id');
        $element->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($element);
        $element->setDecorators(array('ViewHelper'));

        $element = $this->type;
        $element->setLabel(_('#Relationship with us: '))
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'five columns')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns')),
                ))
                ->setOptions(array('class' => 'Full alpha omega'))
                ->setRequired(true);

        $this->removeElement('areaCode');
        $this->removeElement('localNumber');
    }

    public function process($data) {

        $db = Zend_Registry::get('db');
        $contactMapper = new C3op_Register_ContactMapper($db);

        if ($this->isValid($data) !== true) {
            throw new C3op_Form_ContactEditException(_('#Invalid data!'));
        } else {
            $id = $data['id'];
            $contact = $contactMapper->findById($id);
            $contact->SetName($data['name']);

            $contact->SetType($data['type']);
            $contactMapper->update($contact);
            return $id;
        }
    }
}