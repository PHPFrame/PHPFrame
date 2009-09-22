<?php
class PHPFrame_Group extends PHPFrame_PersistentObject
{
    protected $name;
    
    public function __construct(array $options=null)
    {
        $this->addFilter("name", "varchar", 50);
        
        parent::__construct($options);
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($str)
    {
        $this->name = $this->validate("name", $str);
    }
}
