<?php
class MyPersistentObject extends PHPFrame_PersistentObject
{
    public function __construct(array $options=null)
    {
        // Some example fields...
        // Add fields before calling parent's constructor
        $this->addField(
            "name", 
            null, 
            false, 
            new PHPFrame_StringFilter(array("min_length"=>0, "max_length"=>50))
        );
        $this->addField(
            "amount", 
            false, 
            false, 
            new PHPFrame_FloatFilter()
        );
        $this->addField(
            "email", 
            false, 
            false, 
            new PHPFrame_EmailFilter()
        );
        
        parent::__construct($options);
    }
}
