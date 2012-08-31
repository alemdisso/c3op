<?php
class C3op_Form_LinkageEmailEdit extends C3op_Form_EmailEdit
{
    public function __construct($options = null)
    {
        parent::__construct($options);

        $this->setName('newEmailForm')
            ->setAction('/register/linkage/change-email')
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

        $linkage = new Zend_Form_Element_Hidden('linkage');
        $linkage->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($linkage);



    }

    public function process($data) {
        if ($this->isValid($data) !== true)
        {
            throw new C3op_Form_LinkageCreateException('Invalid data!');
        }
        else
        {
            $db = Zend_Registry::get('db');
            $linkageMapper = new C3op_Register_LinkageMapper($db);
            $linkage = $linkageMapper->findById($this->linkage->GetValue());
            if ($this->email->GetValue() != "") {
                $emails = $linkage->GetEmails();
                if (isset($emails[$this->id->GetValue()])) {
                    $email = new C3op_Register_LinkageEmail();
                    $email->SetId($this->id->GetValue());
                    $email->SetEmail($this->email->GetValue());
                    $email->SetLabel($this->label->GetValue());
                    $emails[$this->id->GetValue()] = $email;
                    $linkage->SetEmails($emails);
                    $linkageMapper->update($linkage);
                    return $linkage->GetId();

                } else {
                    throw new C3op_Form_LinkageEditException('Can\'t find this email id at this linkage email list');
                }
            }


        }
    }
}