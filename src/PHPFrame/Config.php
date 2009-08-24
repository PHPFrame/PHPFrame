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
     * Full path to XML file with data
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
    
    public static function instance($path)
    {
        $path = (string) trim($path);
        
        if (!isset(self::$_instances[$path])) {
            self::$_instances[$path] = new self($path);
        }
        
        return self::$_instances[$path];
    }
    
    public function get($key)
    {
        $key = strtolower(trim((string) $key));
        
        preg_match('/([a-zA-Z_]+)\.?(.*)?/', $key, $matches);
        
        if (isset($matches[2]) && !empty($matches[2])) {
            $section = $matches[1];
            $key = $matches[2];
        } else {
            $section = "general";
            $key = $matches[1];
        }

        $this->_ensureKeyExists($section, $key);
        
        return $this->_data[$section][$key];
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
    public function set($key, $value, $section="general")
    {
        $this->_ensureKeyExists($section);
        
        $this->_data[$section][$key] = $value;
        
        return $this; 
    }
    
    public function bind($array)
    {
        if (!is_array($array)) {
            $msg = get_class($this)."::bind() ";
            $msg .= "expected an array as only argument.";
            trigger_error($msg);
        }
        
        foreach ($array as $section=>$value) {
            var_dump($section, $value); 
//            if (array_key_exists($key, $this->_data)) {
//                foreach ($this->_data[$key] as $k=>$v) {
//                    if ($a) {
//                        $this->set($key, $value);
//                    }
//                }
//            }
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
        
        // Store object as string in filesystem
        // This will throw an exception on failure
        PHPFrame_Utils_Filesystem::write($this->_path, (string) $this);
    }
    
    public function toArray()
    {
        return $this->_data;
    }
    
    private function _fetchData()
    {
        $array = parse_ini_file($this->_path, true);
        $this->_data = $array;
    }
    
    private function _ensureKeyExists($section, $key=null)
    {
        if (!isset($this->_data[$section])) {
            $msg = "Configuration file (".$this->_path.") does not containg section ";
            $msg .= $section;
            trigger_error($msg, E_USER_ERROR);
        }
        
        if (!is_null($key)) {
            if (!isset($this->_data[$section][$key])) {
                $msg = "Configuration file (".$this->_path.") does not containg key ";
                $msg .= $key." under section [".$section."]";
                trigger_error($msg, E_USER_ERROR);
            }
        }
    }
}
