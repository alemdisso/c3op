<?php

class C3op_Auth_UserBase {
	
    protected $id;
    protected $login;
    protected $rawPassword;
    protected $email;
    protected $status;
    protected $firstLogin = "0000-00-00";
    protected $lastLogin = "0000-00-00";
    
	
    function __construct($id=0) {
        $this->id = (int)$id;
    }

    public function GetId()
    {
        return $this->id;
    } //GetId

    public function SetId($id)
    {
        if (($this->id == 0) && ($id > 0)) {
            $this->id = (int)$id;
        } else {
            throw new C3op_Auth_UserException('It\'s not possible to change a project\'s ID');
        }
    } //SetId

    public function GetLogin()
    {
        return $this->login;
    } //GetLogin
	
    public function SetLogin($login)
    {
        $validator = new C3op_Auth_UserValidLogin();
        if ($validator->isValid($login)) {
            if ($this->login != $login) {
                $this->login = $login;
            }
        } else {
            throw new C3op_Auth_UserException("This ($login) is not a valid login.");
        }
    } //SetLogin

    public function GetRawPassword()
    {
        return $this->rawPassword;
    }
	
    public function SetPassword($password) 
    {
        if ($this->password != "") {
            $this->rawPassword = $password;
        }
    }

    public function GetEmail()
    {
        return $this->email;
    } //GetEmail
	
    public function SetEmail($email)
    {
        $validator = new C3op_Util_ValidLongString();
        if ($validator->isValid($email)) {
            if ($this->email != $email) {
                $this->email = $email;
            }
        } else {
            throw new C3op_Auth_UserException("This ($email) is not a valid email.");
        }
    } //SetEmail

     
    public function SetStatus($status) 
    {
        $validator = new C3op_Util_ValidPositiveFloat();
        
        if ($validator->isValid($status)) {
            $this->status = (float) $status;
        } else {
            throw new C3op_Auth_UserException("Status must be a positive number.");
        }
    }

    public function GetStatus() 
    {
        return $this->status;
    }
    
    public function GetFirstLogin()
    {
        return $this->firstLogin;
    } //GetFirstLogin
	
    public function SetFirstLogin($firstLogin)
    {
        if ($firstLogin != "") {
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($firstLogin)) {
                if ($this->firstLogin != $firstLogin) {
                    $this->firstLogin = $firstLogin;
                }
            } else {
                throw new C3op_Auth_UserException("This ($firstLogin) is not a valid date of begin.");
            }
        }
    } //SetFirstLogin

    public function GetLastLogin()
    {
        return $this->lastLogin;
    } //GetLastLogin
	
    public function SetLastLogin($lastLogin)
    {
        if ($lastLogin != "") {
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($lastLogin)) {
                if ($this->lastLogin != $lastLogin) {
                    $this->lastLogin = $lastLogin;
                }
            } else {
                throw new C3op_Auth_UserException("This ($lastLogin) is not a valid date of begin.");
            }
        }
    } //SetLastLogin


}