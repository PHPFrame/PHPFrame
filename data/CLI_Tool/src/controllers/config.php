<?php
class ConfigController extends PHPFrame_MVC_ActionController
{
    private $_config=null;
    
    public function __construct()
    {
        $path = getcwd().DS."etc".DS."config.xml";
        if (!is_file($path)) {
            $msg = "Cannot load config File";
            throw new PHPFrame_Exception($msg);
        }
        
        $this->_config = PHPFrame_Config::instance($path);
        
        parent::__construct("list_all");
    }
    
    public function list_all()
    {
        foreach ($this->_config->toArray() as $key=>$value) {
            echo $key.": ".$value['value']."\n";
        }
    }
    
    public function get($key)
    {
        $key = (string) trim($key);
        
        echo $key.": ".$this->_config->get($key)."\n";
    }
    
    public function set($key, $value)
    {
        $key = (string) trim($key);
        $value = (string) trim($value);
        
        $this->_config->set($key, $value);
        
        echo "New value is: ".$value."\n";
    }
}
