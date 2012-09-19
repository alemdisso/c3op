<?php
class C3op_Form_UserCreate extends Zend_Form
{
    public function init()
    {

        // initialize form
        $this->setName('newUserForm')
            ->setAction('/auth/user/create')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        $name = new Zend_Form_Element_Text('name');
        $nameValidator = new C3op_Util_ValidString();
        $name->setLabel('Nome:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eight columns')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setRequired(true)
            ->addValidator($nameValidator)
            ->addFilter('StringTrim')
                ;
        // attach elements to form
        $this->addElement($name);



        $role = new Zend_Form_Element_Select('role');
        $role->setLabel('Papel')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'two columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'one column Right')),
              ))
              ->setOptions(array('class' => 'Full alpha omega'))
              ->setRequired(true);
        $rolesObj = new C3op_Access_Roles();
        $roles = $rolesObj->AllRoles();
        $role->addMultiOption(null, "(escolha um tipo)");
        while (list($key, $title) = each($roles)) {
            $role->addMultiOption($key, $title);
        }
        $this->addElement($role);

        $login = new Zend_Form_Element_Text('login');
        $loginValidator = new C3op_Util_ValidString();
        $login->setLabel('Login:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'two columns')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setRequired(true)
            ->addValidator($loginValidator)
            ->addFilter('StringTrim')
                ;
        // attach elements to form
        $this->addElement($login);

        $password = new Zend_Form_Element_Password('password');
        $passwordValidator = new C3op_Util_ValidString();
        $password->setLabel('Senha:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'two columns')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'one column Right')),
              ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setRequired(true)
            ->addValidator($passwordValidator)
            ->addFilter('StringTrim')
                ;
        // attach elements to form
        $this->addElement($password);

        $email = new Zend_Form_Element_Text('email');
        $emailValidator = new C3op_Util_ValidEmail();
        $email->setLabel('Email:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'five columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'one column Right')),
              ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setRequired(true)
            ->addValidator($emailValidator)
            ->addFilter('StringTrim')
                ;
        // attach elements to form
        $this->addElement($email);

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
            throw new C3op_Form_UserCreateException('Invalid data!');
        }
        else
        {
            $db = Zend_Registry::get('db');
            $userMapper = new C3op_Auth_UserMapper($db);
            $user = new C3op_Auth_User();
            $user->SetLogin($this->login->GetValue());
            $user->SetName($this->name->GetValue());
            $user->SetPassword($this->password->GetValue());
            $user->SetEmail($this->email->GetValue());
            $user->SetRole($this->role->GetValue());
            $userMapper->insert($user);
        }
    }
}