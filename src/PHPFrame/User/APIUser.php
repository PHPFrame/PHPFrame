<?php
class PHPFrame_APIUser extends PHPFrame_PersistentObject
{
    protected $user;
    protected $key;
    
    public function __construct(array $options=null)
    {
        $this->addFilter("user", "varchar", 50);
        $this->addFilter("key", "varchar", 100);
        
        parent::__construct($options);
    }
    
    public function getUser()
    {
        return $this->user;
    }
    
    public function setUser($str)
    {
        $this->user = $this->validate("user", $str);
    }
    
    public function getKey()
    {
        return $this->key;
    }
    
    public function setKey($str)
    {
        $this->key = $this->validate("key", $str);
    }
}
