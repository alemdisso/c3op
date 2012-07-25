<?php
class C3op_Form_UserEdit extends C3op_Form_UserCreate
{
    public function init()
    {
        parent::init();

        // initialize form
        $this->setName('editUserForm')
            ->setAction('/auth/user/edit')
            ->setMethod('post');
        
        $password = $this->getElement('password');
        $password->setRequired(false);
        
        $id = new Zend_Form_Element_Hidden('id');
        $id->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');        
        $this->addElement($id);
        
                        

    }
    
    public function process($data) {
        
        $db = Zend_Registry::get('db');
        $userMapper = new C3op_Auth_UserMapper($db);        
        
        if ($this->isValid($data) !== true) {
            throw new C3op_Form_UserEditException('Invalid data!');
        } else {
            $id = $data['id'];
            $user = $userMapper->findById($id);      
            $user->SetName($data['name']);
            $user->SetLogin($data['login']);
            $user->SetEmail($data['email']);
            $user->SetRole($data['role']);
            
            if ($data['password'] != "") {
                $user->SetPassword($data['password']);
            }
            $userMapper->update($user);
        }
    }
 }