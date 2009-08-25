<?php
/**
 * PHPFrame/Config.php
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
    private static $_instances = array();
    /**
     * Full path to ini file with data
     * 
     * @var string
     */
    private $_path = null;
    /**
     * Array holding config data
     * 
     * @var array
     */
    private $_data = array();
    
    /**
     * Constructor
     * 
     * Private constructor ensures singleton pattern. Use the instance() method to get an instance
     * of this class.
     *
     * @param string $path Full path to ini file with data
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
    
    /**
     * Convert object to string
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        $str  = "; This is a configuration file\n";
        $str .= "; Comments start with ';', as in php.ini\n\n";
        
        foreach ($this->_data as $section_name=>$section_value) {
            $str .= "[".$section_name."]\n\n";
            
            if (is_array($section_value)) {
                foreach ($section_value as $param_name=>$param_value) {
                    $str .= $param_name." = ".$param_value."\n";
                }
            }
            
            $str .= "\n";
        }
        
        return $str;
    }
    
    /**
     * Get singleton instance of config class for a given path
     * 
     * @param string $path
     * 
     * @access public
     * @return PHPFrame_Config
     * @since  1.0
     */
    public static function instance($path)
    {
        $path = (string) trim($path);
        
        if (!isset(self::$_instances[$path])) {
            self::$_instances[$path] = new self($path);
        }
        
        return self::$_instances[$path];
    }
    
    /**
     * Get config key
     * 
     * @param string $key
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function get($key)
    {
        // Make sure the key actually exists in internal array
        $this->keyExists($key, true);
        
        // Parse key string to section and key
        list($section, $key) = $this->_parseKey($key);
        
        // Return value from internal array
        return $this->_data[$section][$key];
    }
    
    /**
     * Set config param
     *
     * This method returns the current instance allowing for fluent syntax
     *
     * @param string $key   The config key we want to set (ie: debug.enable)
     * @param array  $value The new value for the config key
     *
     * @access public
     * @return void
     * @since  1.0
     */
    public function set($key, $value)
    {
        // Make sure the key actually exists in internal array
        $this->keyExists($key, true);
        
        // Parse key string to section and key
        list($section, $key) = $this->_parseKey($key);
        
        // Set value in internal array
        $this->_data[$section][$key] = $value;
    }
    
    /**
     * Bind array to config object
     * 
     * @param array $array
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function bind(array $array)
    {   
        foreach ($array as $key=>$value) {
            if ($this->keyExists($key)) {
                $this->set($key, $value);
            }
        }
    }
    
    /**
     * Get config sections
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getSections()
    {
        return array_keys($this->_data);
    }
    
    /**
     * Get config keys
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getKeys()
    {
        $array = array();
        
        foreach ($this->_data as $section_name=>$section_value) {
            if (is_array($section_value)) {
                foreach ($section_value as $param_key=>$param_value) {
                    if ($section_name != "general") {
                        $param_key = $section_name.".".$param_key;
                    }
                    
                    $array[] = $param_key;
                }
            }
        }
        
        return $array;
    }
    
    /**
     * Ensure a given key exists in internal array
     * 
     * @param string $key
     * @param bool   $ensure If set to TRUE method will trigger error
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function keyExists($str=null, $ensure=false)
    {
        list($section, $key) = $this->_parseKey($str);
        
        if (
            !isset($this->_data[$section]) 
            || !isset($this->_data[$section][$key])
        ) {
            if ($ensure) {
                $msg  = "Configuration file (".$this->_path.") ";
                $msg .= "does not containg key ";
                $msg .= "'".$str."'";
                trigger_error($msg, E_USER_ERROR);
            } else {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Store config object in filesystem as ini file
     *
     * @param string $path Full path to ini file with data
     *
     * @access public
     * @return void
     * @since  1.0
     */
    public function store($path=null)
    {
        if (!is_null($path)) {
            $this->_path = trim((string) $path);
        }
        
        // Store object as string in filesystem
        // This will throw an exception on failure
        $file = new PHPFrame_FS_FileObject($this->_path, "w");
        $file->fwrite((string) $this);
    }
    
    /**
     * Fetch data from ini file
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function _fetchData()
    {
        $array = parse_ini_file($this->_path, true);
        $this->_data = $array;
    }
    
    /**
     * Parse key string into section and key
     * 
     * @access private
     * @return array
     * @since  1.0
     */
    private function _parseKey($str)
    {
        $str = strtolower(trim((string) $str));
        
        preg_match('/([a-zA-Z_]+)\.?(.*)?/', $str, $matches);
        
        if (isset($matches[2]) && !empty($matches[2])) {
            $section = $matches[1];
            $key = $matches[2];
        } else {
            $section = "general";
            $key = $matches[1];
        }
        
        return array($section, $key);
    }
}
