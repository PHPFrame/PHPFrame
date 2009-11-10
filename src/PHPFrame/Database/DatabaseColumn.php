<?php
class PHPFrame_DatabaseColumn implements IteratorAggregate
{
    const TYPE_BOOL           = "boolean";
    const TYPE_TINYINT        = "tinyint";
    const TYPE_SMALLINT       = "smallint";
    const TYPE_MEDIUMINT      = "mediumint";
    const TYPE_INT            = "int";
    const TYPE_BIGINT         = "bigint";
    const TYPE_FLOAT          = "float";
    const TYPE_CHAR           = "char";
    const TYPE_VARCHAR        = "varchar";
    const TYPE_TEXT           = "text";
    const TYPE_BLOB           = "blob";
    const TYPE_DATETIME       = "datetime";
    const TYPE_DATE           = "date";
    const TYPE_TIME           = "time";
    const TYPE_YEAR           = "year";
    const TYPE_TIMESTAMP      = "timestamp";
    const TYPE_ENUM           = "enum";
    const TYPE_BINARY         = "binary";
    const EXTRA_AUTOINCREMENT = "auto_increment";
    const KEY_PRIMARY         = "PRI";
    const KEY_UNIQUE          = "UNI";
    const KEY_MULTIPLE        = "MUL";
    
    private $_name, $_type, $_null, $_key, $_default, $_extra, $_reflection_obj;
    
    public function __construct(array $options=null)
    {
        $this->_reflection_obj = new ReflectionClass($this);
        
        if (!is_null($options)) {
            foreach ($options as $key=>$value) {
                $setter = "set".ucwords(str_replace("_", " ", $key));
                $setter = str_replace(" ", "", $setter);
                
                if (method_exists($this, $setter)) {
                    $this->$setter($value);
                }
            }
        }
        
        if (is_null($this->_name)) {
            $msg  = "Option 'name' is required in ";
            $msg .= get_class($this)."::".__FUNCTION__."().";
            throw new InvalidArgumentException($msg);
        }
    }
    
    public function getIterator()
    {
        $props = get_object_vars($this);
        $array = array();
        
        foreach ($props as $key=>$value) {
            if ($key == "_reflection_obj") {
                continue;
            }
            
            $array[substr($key, 1)] = $value;
        }
        
        return new ArrayIterator($array);
    }
    
    public function getName()
    {
        return $this->_name;
    }
    
    public function getType()
    {
        return $this->_type;
    }
    
    public function getNull()
    {
        return $this->_null;
    }
    
    public function getKey()
    {
        return $this->_key;
    }
    
    public function getDefault()
    {
        return $this->_default;
    }
    
    public function getExtra()
    {
        return $this->_extra;
    }
    
    public function setName($str)
    {
        $this->_name = $str;
    }
    
    public function setType($str)
    {
        if (!is_string($str)) {
            $msg  = "Column type must be a string. See ";
            $msg .= get_class($this)." constants";
            throw new InvalidArgumentException($msg);
        }
        
        $types = array();
        foreach ($this->_reflection_obj->getConstants() as $key=>$value) {
            if (strpos($key, "TYPE_") === 0) {
                $types[] = $value;
            }
        }
        
        if (!in_array($str, $types)) {
            $msg  = "Wrong column type. See ";
            $msg .= get_class($this)." constants";
            throw new InvalidArgumentException($msg);
        }
        
        $this->_type = $str;
    }
    
    public function setNull($bool)
    {
        $this->_null = $bool;
    }
    
    public function setKey($str)
    {
        $this->_key = $str;
    }
    
    public function setDefault($str)
    {
        $this->_default = $str;
    }
    
    public function setExtra($str)
    {
        $this->_extra = $str;
    }
}
