<?php
class C3op_Form_UserLogin extends Zend_Form
{
    public function init()
    {

        // initialize form
        $this->setName('loginForm')
            ->setAction('/auth/login')
            ->setMethod('post');
        
        // create text input for name
        $login = new Zend_Form_Element_Text('login');
        $loginValidator = new C3op_Register_ContactValidName();
        $login->setLabel('Usuário:')
            ->setOptions(array('size' => '50'))
            ->setRequired(true)
            ->addValidator($loginValidator)
            ->addFilter('StringTrim')
                ;
        // attach elements to form
        $this->addElement($login);
        
        // create text input for name
        $password = new Zend_Form_Element_Password('password');
        $passwordValidator = new C3op_Register_ContactValidName();
        $password->setLabel('Senha:')
            ->setOptions(array('size' => '50'))
            ->setRequired(true)
            ->addValidator($passwordValidator)
            ->addFilter('StringTrim')
                ;
        // attach elements to form
        $this->addElement($password);
        
                
        // create submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Salvar')
            ->setOptions(array('class' => 'submit'));
        $this->addElement($submit);

    }
    
    public function process($data) {
        if ($this->isValid($data) !== true) 
        {
            throw new C3op_Form_UserLoginException('Invalid data!');
        } 
        else
        {

            $adapter = new C3op_Auth_Adapter_Mapper($this->login->GetValue(), $this->password->GetValue());
            $auth = Zend_Auth::getInstance();
            $result = $auth->authenticate($adapter);
            $user = $adapter->getAuthenticatedUser();
            //die("...{$user->GetId()}");
            
            if ($user instanceOf C3op_Auth_User) {
                return $user;
            } else {
                
                $this->view->message = 'You could not be logged in. Please try again.';
                return false;
            }
        }
    }

    public function successAction()
    {
        if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $this->view->messages = $this->_helper
                ->getHelper('FlashMessenger')
                ->getMessages();
        } else {
            $this->_redirect('/');
        }
    }    
    
}