<?php
class C3op_Form_UserCreate extends Zend_Form
{
    public function init()
    {

        // initialize form
        $this->setName('newUserForm')
            ->setAction('/auth/user/create')
            ->setMethod('post');
        
        $login = new Zend_Form_Element_Text('login');
        $loginValidator = new C3op_Util_ValidString();
        $login->setLabel('Login:')
            ->setOptions(array('size' => '50'))
            ->setRequired(true)
            ->addValidator($loginValidator)
            ->addFilter('StringTrim')
                ;
        // attach elements to form
        $this->addElement($login);
        
        $name = new Zend_Form_Element_Text('name');
        $nameValidator = new C3op_Util_ValidString();
        $name->setLabel('Nome:')
            ->setOptions(array('size' => '50'))
            ->setRequired(true)
            ->addValidator($nameValidator)
            ->addFilter('StringTrim')
                ;
        // attach elements to form
        $this->addElement($name);
        
        $password = new Zend_Form_Element_Password('password');
        $passwordValidator = new C3op_Util_ValidString();
        $password->setLabel('Senha:')
            ->setOptions(array('size' => '50'))
            ->setRequired(true)
            ->addValidator($passwordValidator)
            ->addFilter('StringTrim')
                ;
        // attach elements to form
        $this->addElement($password);
        
        $email = new Zend_Form_Element_Text('email');
        $emailValidator = new C3op_Util_ValidEmail();
        $email->setLabel('Email:')
            ->setOptions(array('size' => '50'))
            ->setRequired(true)
            ->addValidator($emailValidator)
            ->addFilter('StringTrim')
                ;
        // attach elements to form
        $this->addElement($email);
        
        $role = new Zend_Form_Element_Select('role');
        $role->setLabel('Papel');
        $titleRoles = C3op_Access_Roles::AllRoles();
        $role->addMultiOption(null, "(escolha um tipo)");
        while (list($key, $title) = each($titleRoles)) {
            $role->addMultiOption($key, $title);
        }        
        $this->addElement($role);

        // create submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Salvar')
            ->setOptions(array('class' => 'submit'));
        $this->addElement($submit);

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