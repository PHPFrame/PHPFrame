<?php
class PHPFrame_Config_INI extends PHPFrame_Config
{
    protected function __construct($path=null)
    {
        parent::__construct($path);
    }
    
    public function toString()
    {
        $str = "";
        
        return $str;
    }
    
    protected function _fetchData()
    {
        $array = parse_ini_file($this->_path, true);
        $this->data = $array;
    }
}
