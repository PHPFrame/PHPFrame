<?php
class PHPFrame_Organisation extends PHPFrame_PersistentObject
{
    public function __construct(array $options=null)
    {
        $this->addField(
           "name", 
           null, 
           false,  
           new PHPFrame_StringFilter(array("min_length"=>3, "max_length"=>50))
        );
        
        parent::__construct($options);
    }
    
    public function getName()
    {
        return $this->fields["name"];
    }
    
    public function setName($str)
    {
        $this->fields["name"] = $this->validate("name", $str);
    }
}
