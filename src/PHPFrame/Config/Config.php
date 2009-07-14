<?php
class PHPFrame_Config
{
    private $_path=null;
    private $_data=array();
    
    public function __construct($path=null)
    {
		if (!is_null($path)) {
			$this->_path = (string) $path;
		} else {
			$data_dir = PEAR_Config::singleton()->get('data_dir');
			$this->_path = $data_dir;
			$this->_path .= DIRECTORY_SEPARATOR."PHPFrame";
			$this->_path .= DIRECTORY_SEPARATOR."config.xml";
		}
        
        // Fetch data from file
        $this->_fetchData();
    }
    
    public function __toString()
    {
        return $this->toString();
    }
    
    public function __get($key)
    {
        return $this->get($key);
    }
    
    public function get($key)
    {
        if (!isset($this->_data[$key])) {
            return null;
        }
        
        return $this->_data[$key];
    }
    
    public function toString()
    {
        $str = "";
        
        return $str;
    }
    
    public function toArray()
    {
        return $this->_data;
    }
    
    private function _fetchData()
    {
        $xml = simplexml_load_file($this->_path);
        
        //var_dump($xml);
    }
}