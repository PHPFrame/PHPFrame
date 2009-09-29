<?php
class PHPFrame_ParamDoc
{
    private $_array = array("name"=>null, "type"=>null, "description"=>null);
    
    public function __construct(ReflectionParameter $reflection_param)
    {
        $this->_array["name"] = $reflection_param->getName();
        
        $doc_comment = (string) $reflection_param->getDeclaringFunction()
                                                   ->getDocComment();
        $this->_parseDocComment($doc_comment);
    }
    
    public function __toString()
    {
        $str = "";
        
        if ($this->getType()) {
            $str .= $this->getType()." ";
        }
        
        $str .= "$".$this->getName();
        
        if ($this->getDescription()) {
            $str .= " ".$this->getDescription();
        }
        
        return $str;
    }
    
    public function getIterator()
    {
        return new ArrayIterator($this->_array);
    }
    
    public function getName()
    {
        return $this->_array["name"];
    }
    
    public function getType()
    {
        return $this->_array["type"];
    }
    
    public function getDescription()
    {
        return $this->_array["description"];
    }
    
    private function _parseDocComment($str)
    {
        if (!is_string($str)) {
            $msg = "Argument \$str must be of type string.";
            throw new InvalidArgumentException($msg);
        }
        
        $array = explode("\n", $str);
        
        foreach ($array as $line) {
            $line = trim($line, "/*\n\t ");
            
            if (empty($line)) {
                continue;
            }
            
            $pattern = '/^@param\s+([\w|]+)\s+\$'.$this->getName().'(\s+)?(.*)?/';
            if (preg_match($pattern, $line, $matches)) {
                $this->_array["type"] = $matches[1];
                $this->_array["description"] = $matches[3];
            }
        }
    }
}