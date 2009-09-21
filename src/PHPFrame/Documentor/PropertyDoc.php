<?php
class PHPFrame_PropertyDoc implements IteratorAggregate
{
    private $_array = array(
        "name"            => null, 
        "access"          => null,
        "type"            => null, 
        "declaring_class" => null
    );
    
    public function __construct(ReflectionProperty $reflection_prop)
    {
        $this->_array["name"] = $reflection_prop->getName();
        
        if ($reflection_prop->isPublic()) {
            $this->_array["access"] = "public";
        } elseif ($reflection_prop->isProtected()) {
            $this->_array["access"] = "protected";
        } elseif ($reflection_prop->isPrivate()) {
            $this->_array["access"] = "private";
        }
        
        $this->_array["declaring_class"] = $reflection_prop->getDeclaringClass()
                                                           ->getName();
    }
    
    public function __toString()
    {
        $str  = $this->_array["name"]." (".$this->_array["access"].") - ";
        $str .= $this->_array["declaring_class"]."\n";
        
        return $str;
    }
    
    public function getIterator()
    {
        return new ArrayIterator($this->_array);
    }
}
