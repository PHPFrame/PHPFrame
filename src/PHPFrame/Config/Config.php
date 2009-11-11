<?php
/**
 * PHPFrame/Config/Config.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Config
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * This class produces objects that are used to manage data stored in ini files. 
 * In applications built using the provided application template this class is
 * used to manage the global configuration options stored in etc/phpframe.ini.
 * 
 * Config objects are traversable because this class implements the 
 * IteratorAggregate interface. This means that instances can be used as an array 
 * in foreach loops.
 * 
 * Iteration example:
 * 
 * <code>
 * // this code would be inside a controller
 * $config = $this->getApp()->getConfig();
 * foreach ($config as $key=>$value) {
 *     echo $key.': '.$value;
 * }
 * </code>
 * 
 * @category PHPFrame
 * @package  Config
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @uses     PHPFrame_FileObject, IteratorAggregate
 * @since    1.0
 */
class PHPFrame_Config implements IteratorAggregate
{
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
     * @param string $path Full path to ini file with data
     *
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct($path)
    {
        $this->_path = trim((string) $path);
        
        // Fetch data from file
        $this->_fetchData();
    }
    
    /**
     * Convert object to string
     * 
     * This method is automatically called when trying to use the object as a string 
     * or by explicitly casting it to string.
     * 
     * Example:
     * 
     * <code>
     * $config = PHPFrame::Config();
     * echo $config;
     * </code>
     * 
     * The above example should print the config object as a string in the ini 
     * format.
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
     * Get iterator
     * 
     * This method implements the IteratorAggregate interface and thus makes config 
     * objects traversable, hooking to the foreach construct.
     * 
     * @access public
     * @return ArrayIterator
     * @since  1.0
     */
    public function getIterator()
    {
        $array = array();
        
        foreach ($this->getKeys() as $key) {
            $array[$key] = $this->get($key);
        }
        
        return new RecursiveArrayIterator($array);
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
     * @param string $key   The config key we want to set (ie: debug.log_level)
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
            // Replace section separator "_" with "." This hack is needed 
            // because HTTP post vars have them automatically replaced
            foreach ($this->getSections() as $section) {
                $filtered_key = preg_replace(
                    '/('.$section.'_)/i', 
                    $section.".", 
                    $key, 
                    -1, 
                    $count
                );
                
                if ($count > 0) {
                    $key = $filtered_key;
                }
            }
            
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
     * Get section data as an associative array
     * 
     * @param string $str
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getSection($section_name)
    {
        if (!array_key_exists($section_name, $this->_data)) {
            $msg = "Section '".$section_name."' doesn't exist in config.";
            throw new RuntimeException($msg);
        }
        
        return $this->_data[$section_name];
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
     * Ensure a given key exists in internal array.
     * 
     * @param string $str    A string with the key we want to check.
     * @param bool   $ensure If set to TRUE method will trigger error.
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
        $file = new PHPFrame_FileObject($this->_path, "w");
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
        if (!$array = @parse_ini_file($this->_path, true)) {
            $msg = "Could not load configuration file ".$this->_path;
            trigger_error($msg, E_USER_ERROR);
        }
        
        $this->_data = $array;
    }
    
    /**
     * Parse key string into section and key
     * 
     * @param string $str
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
