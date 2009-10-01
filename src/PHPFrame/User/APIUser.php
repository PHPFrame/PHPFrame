<?php
class PHPFrame_APIUser extends PHPFrame_PersistentObject
{
    public function __construct(array $options=null)
    {
    	$this->addField(
           "user", 
           null, 
           false,  
           new PHPFrame_StringFilter(array("min_length"=>6, "max_length"=>50))
        );
        $this->addField(
           "key", 
           null, 
           false,  
           new PHPFrame_StringFilter(array("min_length"=>50, "max_length"=>50))
        );
        
        parent::__construct($options);
    }
    
    public function getUser()
    {
        return $this->fields["user"];
    }
    
    public function setUser($str)
    {
        $this->fields["user"] = $this->validate("user", $str);
    }
    
    public function getKey()
    {
        return $this->fields["key"];
    }
    
    public function setKey($str)
    {
        $this->fields["key"] = $this->validate("key", $str);
    }
}
