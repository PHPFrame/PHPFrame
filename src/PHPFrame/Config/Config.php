<?php
class PHPFrame_Config
{
    private $_path=null;
    private $_data=array();
    
    public function __construct($path=null)
    {
        // Set path to xml file
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
    
    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }
    
    public function get($key)
    {
        if (!isset($this->_data[$key])) {
            return null;
        }
        
        return $this->_data[$key];
    }
    
    public function set($key, $value)
    {
        if (!isset($this->_data[$key])) {
            return null;
        }
        
        $this->_data[$key] = $value;
    }
    
    public function bind($array)
    {
        if (!is_array($array)) {
            $msg = get_class($this)."::bind() ";
            $msg .= "expected an array as only argument.";
            trigger_error($msg);
        }
        
        $keys = $this->getKeys();
        
        foreach ($array as $key=>$value) {
            if (in_array($key, $keys)) {
                $this->set($key, $value);
            }
        }
        
    }
    
    public function getKeys()
    {
        return array_keys($this->toArray());
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
    
    public function toXML()
    {
        $xml = new DOMDocument();
        
        return $xml->saveXML();
    }
    
    private function _fetchData()
    {
        $xml = simplexml_load_file($this->_path);
        
        if (!$xml instanceof SimpleXMLElement) {
            $msg = get_class($this).": ";
            $msg .= "Could not load config from xml file.";
            trigger_error($msg);
        }
        
        foreach ($xml->data as $data) {
            $array["name"] = trim((string) $data->name);
            $array["def_value"] = trim((string) $data->def_value);
            $array["description"] = trim((string) $data->description);
			$array["value"] = trim((string) $data->value);

			if ($array["value"] == "" && $array["def_value"] != "") {
				$array["value"] = $array["def_value"];
			}
            
            $this->_data[$array["name"]] = $array;
        }
    }
}