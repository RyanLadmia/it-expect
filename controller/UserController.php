<?php

class UserController
{
    private $ModelUser;

    public function __construct()
    {
        $this->ModelUser = new ModelUser();
    }

    public function register($firstame, $lastname, $email, $password)
    {
        $result = $this->ModelUser->register($firstame, $lastname, $email, $password);
        return $result;
    }

    public function login($email, $password)
    {
        $result = $this->ModelUser->login($email, $password);
        return $result;
    }

    public function getAllUserInfos($userId)
    {
        return $this->ModelUser->getAllUserInfos($userId);
    }


    public function updateUserField($userId, $field, $value, $confirmPassword = null)
    {
        return $this->ModelUser->updateUserField($userId, $field, $value, $confirmPassword);
    }

    public function deleteUserById($userId)
    {
        return $this->ModelUser->deleteUserById($userId);
    }




    public function sendPasswordResetEmail($email)
    {
        return $this->ModelUser->sendPasswordResetEmail($email);
    }
    
    public function verifyResetToken($token)
    {
        return $this->ModelUser->verifyResetToken($token);
    }
    
    public function updatePassword($token, $newPassword)
    {
        return $this->ModelUser->updatePassword($token, $newPassword);
    }
}
?>