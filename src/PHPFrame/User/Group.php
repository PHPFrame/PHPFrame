<?php
class PHPFrame_Group extends PHPFrame_PersistentObject
{
    public function __construct(array $options=null)
    {
    	// Create the filter for the group name
    	$filter = new PHPFrame_StringFilter(array(
    	   "min_length"=>3, 
    	   "max_length"=>50
    	));
    	// Add the field in the PersistentObject
    	$this->addField("name", null, false,  $filter);
    	
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
