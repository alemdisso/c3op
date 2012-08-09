<?php

class C3op_Auth_User 
{
	
    protected $id;
    protected $login;
    protected $name;
    protected $rawPassword;
    protected $email;
    protected $role;
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
        $validator = new C3op_Util_ValidString();
        if ($validator->isValid($login)) {
            if ($this->login != $login) {
                $this->login = $login;
            }
        } else {
            throw new C3op_Auth_UserException("This ($login) is not a valid login.");
        }
    } //SetLogin

    public function GetName()
    {
        return $this->name;
    } //GetName
	
    public function SetName($name)
    {
        $validator = new C3op_Util_ValidString();
        if ($validator->isValid($name)) {
            if ($this->name != $name) {
                $this->name = $name;
            }
        } else {
            throw new C3op_Auth_UserException("This ($name) is not a valid name.");
        }
    } //SetName

    public function GetRawPassword()
    {
        return $this->rawPassword;
    }
	
    public function SetPassword($password) 
    {
        if ($password != "") {
            $this->rawPassword = $password;
        }
    }

    public function GetEmail()
    {
        return $this->email;
    } //GetEmail
	
    public function SetEmail($email)
    {
        $validator = new C3op_Util_ValidEmail();
        if ($validator->isValid($email)) {
            if ($this->email != $email) {
                $this->email = $email;
            }
        } else {
            throw new C3op_Auth_UserException("This ($email) is not a valid email.");
        }
    } //SetEmail

    public function GetRole() 
    {
        return $this->role;
    }
    
    public function SetRole($role) 
    {
        switch ($role) {
            case C3op_Access_RolesConstants::ROLE_UNKNOWN:
            case C3op_Access_RolesConstants::ROLE_GUEST:
            case C3op_Access_RolesConstants::ROLE_USER:
            case C3op_Access_RolesConstants::ROLE_ASSISTANT:
            case C3op_Access_RolesConstants::ROLE_ADMINISTRATOR:
            case C3op_Access_RolesConstants::ROLE_CONTROLLER:
            case C3op_Access_RolesConstants::ROLE_COORDINATOR:
            case C3op_Access_RolesConstants::ROLE_DIRECTOR:
            case C3op_Access_RolesConstants::ROLE_SYSADMIN:
                $this->role = (int) $role;
                break;
            
            case null:
            case "":
            case 0:
            case false:
                $this->role = C3op_Access_RolesConstants::ROLE_UNKNOWN;
                break;
                 
            default:
                throw new C3op_Access_RolesException("Invalid role.");
                break;
        }
    }

     
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