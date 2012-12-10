<?php
class C3op_Form_ContactRemove extends Zend_Form
{
    public function init()
    {

        // initialize form
        $this->setName('removeContactForm')
            ->setAction('/register/contact/remove')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        $element = new Zend_Form_Element_Hidden('id');
        $element->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($element);
        $element->setDecorators(array('ViewHelper'));


        // remove submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel(_('#Confirm removal'))
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

        $db = Zend_Registry::get('db');
        $contactMapper = new C3op_Register_ContactMapper($db);

        if ($this->isValid($data) !== true)
        {
            throw new C3op_Form_ContactRemoveException(_('#Invalid data!'));
        }
        else
        {
            $id = $data['id'];
            $contact = $contactMapper->findById($id);
            $contactRemoval = new C3op_Register_ContactRemoval($contact, $contactMapper);
            $contactRemoval->remove();
            return $id;
        }
    }
}