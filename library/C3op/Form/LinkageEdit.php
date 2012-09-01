<?php
class C3op_Form_LinkageEdit extends C3op_Form_LinkageCreate
{
    public function __construct($options = null)
    {
        parent::__construct($options);
        //$this->setAction($options['action'])->setMethod('post');
        // initialize form

        $this->setName('editLinkageForm')
            ->setAction('/register/linkage/edit')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        $id = new Zend_Form_Element_Hidden('id');
        $id->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($id);

        $contact = new Zend_Form_Element_Hidden('contact');
        $contact->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($contact);

        $institution = new Zend_Form_Element_Select('institution');
        $institution->setLabel('Instituição: ')
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
        $institution->addMultiOption(0, "(escolha uma...)");
        $this->addElement($institution);

        // $this->addElementText('position', 'Cargo/Função:', new C3op_Util_ValidString(), 50);

        $shortName = new Zend_Form_Element_Text('position');
        $validator = new C3op_Register_InstitutionValidName;
        $shortName->setLabel('Cargo/Função:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setRequired(true)
            ->addValidator($validator)
//            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim')
                ;
        $this->addElement($shortName);

        // $this->addElementText('department', 'Departamento:', new C3op_Util_ValidString(), 50);

        $shortName = new Zend_Form_Element_Text('department');
        $validator = new C3op_Register_InstitutionValidName;
        $shortName->setLabel('Departamento:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setRequired(true)
            ->addValidator($validator)
//            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim')
                ;
        $this->addElement($shortName);

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

    public function init()
    {
        parent::init();

        // initialize form
        $this->setName('editLinkageForm')
            ->setAction('/register/linkage/edit')
            ->setMethod('post');

        $id = new Zend_Form_Element_Hidden('id');
        $id->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($id);



    }

    public function process($data) {

        $db = Zend_Registry::get('db');
        $linkageMapper = new C3op_Register_LinkageMapper($db);
        if ($this->isValid($data) !== true) {
            throw new C3op_Form_LinkageEditException('Invalid data!');
        } else {
            $id = $data['id'];
            $linkage = $linkageMapper->findById($id);
            $linkage->SetDepartment($data['department']);
            $linkage->SetContact($data['contact']);
            $linkage->SetInstitution($data['institution']);
            $linkage->SetPosition($data['position']);

            $linkageMapper->update($linkage);
        }
    }

    private function addElementText($fieldName, $label, $validator, $fieldSize)
    {
        $elementText = new Zend_Form_Element_Text($fieldName);
        $elementText->setLabel($label)
            ->setOptions(array('size' => "$fieldSize"))
            ->addValidator($validator)
            ->addFilter('StringTrim')
                ;
        $this->addElement($elementText);

    }
}