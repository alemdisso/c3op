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
        $login = new Zend_Form_Element_Text('loginLogin');
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
        $password = new Zend_Form_Element_Password('passwordLogin');
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

            $adapter = new C3op_Auth_Adapter_Mapper($this->loginLogin->GetValue(), $this->passwordLogin->GetValue());
            $auth = Zend_Auth::getInstance();
            $result = $auth->authenticate($adapter);
            try {
                $user = $adapter->getAuthenticatedUser();
            } 
            catch (Exception $e) {
                return false;
            }
            
            if ($user instanceOf C3op_Auth_User) {
                return $user;
            } else {
                
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