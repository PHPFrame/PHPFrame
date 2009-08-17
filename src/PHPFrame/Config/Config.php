<?php
/**
 * PHPFrame/Config/Config.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Config
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Config Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Config
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
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
        
        if (
            isset($this->_data[$key]["name"])
            && isset($this->_data[$key]["value"])
        ) {
            return $this->_data[$key]["value"];
        }
        
        return $this->_data[$key];
    }
    
    /**
     * Set config param
     *
     * This method returns the current instance allowing for fluent syntax
     *
     * @param string $key
     * @param array  $value
     *
     * @access public
     * @return PHPFrame_Config
     * @since  1.0
     */
    public function set($key, $value)
    {
        if (!isset($this->_data[$key])) {
            return null;
        }
        
        if (
            isset($this->_data[$key]["name"])
            && isset($this->_data[$key]["value"])
        ) {
            $this->_data[$key]["value"] = $value;
        } else {
            $this->_data[$key] = $value;
        }
        
        return $this; 
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
    
    /**
     * Store config object in filesystem as XML
     *
     * @param string $path Full path to XML file with data
     *
     * @access public
     * @return void
     * @since  1.0
     */
    public function store($path=null)
    {
        if (!is_null($path)) {
            $this->_path = (string) trim($path);
        }
        
        // Store object as XML in filesystem
        // This will throw an exception on failure
        PHPFrame_Utils_Filesystem::write($this->_path, $this->toXML());
    }
    
    public function toString()
    {
        $str = "FIX ME!!! (".get_class($this)."::toString())";
        
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
        
        foreach ($this->toArray() as $row) {
            $data_node = $xml->addNode($config_node, "data", array());
			
			if (is_array($row)) {
				foreach ($row as $key=>$value) {
                    $xml->addNode($data_node, $key, array(), $value);
				}
			}
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
				$this->_data[$array["name"]] = $array;
			} else {
			    $this->_data[] = $array;
			}
        }
    }
}
