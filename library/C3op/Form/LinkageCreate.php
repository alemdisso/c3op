<?php
class C3op_Form_LinkageCreate extends Zend_Form
{
    public function __construct($options = null)
    {
        parent::__construct($options);

        $this->setName('newLinkageForm')
            ->setAction('/register/linkage/create')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        if (isset($options['contact'])) {
            $contact = new Zend_Form_Element_Hidden('contact');
            $contact->addValidator('Int')
                ->addFilter('StringTrim');
            $this->addElement($contact);
        }

        if (isset($options['institution'])) {
            $institution = new Zend_Form_Element_Hidden('institution');
            $institution->addValidator('Int')
                ->addFilter('StringTrim');
            $this->addElement($institution);
        } else {
            $institution = new Zend_Form_Element_Select('institution');
            $institution->setLabel(_('#Institution: '))
                ->setDecorators(array(
                      'ViewHelper',
                      'Errors',
                      array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'nine columns')),
                      array('Description', array('escape' => false, 'tag' => 'div', 'class' => 'two columns omega')),
                      array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                  ))
                ->setOptions(array('class' => 'Full alpha omega'))
                ->setDescription('<a href="/register/institution/create" class="two columns button alpha omega">Nova Instituição</a>')
                ->setRegisterInArrayValidator(false);
            $institution->addMultiOption(0, _("#(linkage.institution.choose one...)"));
            $this->addElement($institution);
        }

        $shortName = new Zend_Form_Element_Text('position');
        $validator = new C3op_Register_InstitutionValidName;
        $shortName->setLabel(_('#Position:')) //Cargo/Função:
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->addValidator($validator)
            ->addFilter('StringTrim')
                ;
        $this->addElement($shortName);

        $shortName = new Zend_Form_Element_Text('department');
        $validator = new C3op_Register_InstitutionValidName;
        $shortName->setLabel(_('#Departament:'))
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->addValidator($validator)
            ->addFilter('StringTrim')
                ;
        $this->addElement($shortName);

        $submit = new Zend_Form_Element_Submit('submit');
        $submit ->setLabel(_('#Submit'))
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
            throw new C3op_Form_LinkageCreateException(_('#Invalid data!'));
        }
        else
        {
            $db = Zend_Registry::get('db');
            $linkageMapper = new C3op_Register_LinkageMapper($db);

            $linkage = new C3op_Register_Linkage();
            $linkage->SetDepartment($this->department->GetValue());
            $linkage->SetPosition($this->position->GetValue());
            $linkage->SetContact($this->contact->GetValue());

            $institution = $this->institution->GetValue();
            $linkage->SetInstitution($institution);

            $linkageMapper->insert($linkage);
            return $linkage->GetId();
        }
    }

}