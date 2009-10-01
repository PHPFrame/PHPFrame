<?php
class Customer extends PHPFrame_PersistentObject
{
    protected $first_name, $last_name, $email;
    
    public function getFirstName()
    {
        return $this->first_name;
    }
    
    public function getLastName()
    {
        return $this->last_name;
    }
    
    public function getEmail()
    {
        return $this->email;
    }
    
    public function setFirstName($value)
    {
        $this->first_name = $value;
    }
    
    public function setLastName($value)
    {
        $this->last_name = $value;
    }
    
    public function setEmail($value)
    {
        $this->email = $value;
    }
}