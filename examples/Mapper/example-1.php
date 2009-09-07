<?php
include_once "PHPFrame.php";

class MyDomainObject extends PHPFrame_DomainObject
{
    protected $first_field, $second_field, $third_field;
    
    public function getFirstField()
    {
        return $this->first_field;
    }
    
    public function getSecondField()
    {
        return $this->second_field;
    }
    
    public function getThirdField()
    {
        return $this->third_field;
    }
    
    public function setFirstField($value)
    {
        $this->first_field = $value;
    }
    
    public function setSecondField($value)
    {
        $this->second_field = $value;
    }
    
    public function setThirdField($value)
    {
        $this->third_field = $value;
    }
}

$my_domain_object = new MyDomainObject(array(
    "first_field"=>"Blah blah", 
    "second_field"=>"Another value"
));

print_r(iterator_to_array($my_domain_object));