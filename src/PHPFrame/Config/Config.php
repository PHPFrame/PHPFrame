<?php
class PHPFrame_Config
{
	/**
	 * Array holding instances of this class
	 * 
	 * @var array
	 */
    private static $_instances=array();
	/**
	 * Full path to XML file with data
	 * 
	 * @var string
	 */
    private $_path=null;
	/**
	 * Array holding config data
	 * 
	 * @var array
	 */
    private $_data=array();
    
	/**
	 * Constructor
	 * 
	 * Private constructor ensures singleton pattern. Use the instance() method to get an instance
	 * of this class.
	 *
	 * @param string $path Full path to XML file with data
	 *
	 * @access private
	 * @return void
	 * @since  1.0
	 */
    private function __construct($path)
    {
        $this->_path = (string) $path;
        
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
    
    public static function instance($path=null)
    {
        // Use default empty distro version of template
		if (is_null($path)) {
		    require_once "PEAR/Config.php";
			$data_dir = PEAR_Config::singleton()->get('data_dir');
			$path = $data_dir;
			$path .= DIRECTORY_SEPARATOR."PHPFrame";
			$path .= DIRECTORY_SEPARATOR."config.xml";
		}
		
        if (!isset(self::$_instances[$path])) {
            self::$_instances[$path] = new self($path);
        }
        
        return self::$_instances[$path];
    }
    
    public function get($key)
    {
        if (!isset($this->_data[$key])) {
            return null;
        }
        
        return $this->_data[$key]["value"];
    }
    
    public function set($key, $value)
    {
        if (!isset($this->_data[$key])) {
            return null;
        }
        
        $this->_data[$key]["value"] = $value;
    }
    
    public function bind($array)
    {
        if (!is_array($array)) {
            $msg = get_class($this)."::bind() ";
            $msg .= "expected an array as only argument.";
            trigger_error($msg);
        }
        
        foreach ($array as $key=>$value) {
            if (array_key_exists($key, $this->_data)) {
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
        $xml = new PHPFrame_Document_XML();
        $config_node = $xml->addNode(null, "config");
        
        foreach ($this->toArray() as $value) {
            $data_node = $xml->addNode($config_node, "data", array());

            $xml->addNode($data_node, "name", array(), $value["name"]);
            $xml->addNode($data_node, "def_value", array(), $value["def_value"]);
            $xml->addNode($data_node, "description", array(), $value["description"]);
            $xml->addNode($data_node, "value", array(), $value["value"]);
        }
        
        return $xml->toString();
    }
    
    private function _fetchData()
    {
        $xml = @simplexml_load_file($this->_path);
        
        if (
			!$xml instanceof SimpleXMLElement
			|| !$xml->data instanceof SimpleXMLElement
		) {
            $msg = get_class($this).": ";
            $msg .= "Could not load config from xml file.";
            trigger_error($msg);
        }
		
		$key = 0;
        foreach ($xml->data as $data) {
			
			if (!$data instanceof SimpleXMLElement) {
				continue;
			}
			
			$array = array();
			foreach ($data as $key=>$value) {
				$array[$key] = (string) $value;
			}
			
			if (
				isset($array["value"]) 
				&& $array["value"] == "" 
				&& isset($array["def_value"]) 
				&& $array["def_value"] != ""
			) {
				$array["value"] = $array["def_value"];
			}
            
			if (isset($array["name"])) {
				$key = $array["name"];
			}
			
            $this->_data[$key] = $array;

			$key++;
        }
    }
}