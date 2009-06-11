<?php
/**
 * PHPFrame/Registry/Application.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Registry
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Application Registry Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Registry
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Registry_Application extends PHPFrame_Registry
{
    /**
     * Instance of itself in order to implement the singleton pattern
     * 
     * @var object of type PHPFrame_Application_FrontController
     */
    private static $_instance=null;
    private $_cache_file=null;
    private $_readonly=array("permissions", "components", "modules");
    private $_array=array();
    
    /**
     * Constructor
     * 
     * @access    protected
     * @return    void
     * @since    1.0
     */
    protected function __construct() 
    {
        // Ensure that cache dir is writable
        PHPFrame_Utils_Filesystem::ensureWritableDir(config::FILESYSTEM.DS."cache");
        
        $this->_cache_file = config::FILESYSTEM.DS."cache".DS."application.registry";
        // Read data from cache
        if (is_file($this->_cache_file)) {
            $serialized_array = file_get_contents($this->_cache_file);
            $this->_array = unserialize($serialized_array);
        }
        else {
            // Re-create data
            $this->_array['permissions'] = new PHPFrame_Application_Permissions();
            $this->_array['components'] = new PHPFrame_Application_Components();
            $this->_array['modules'] = new PHPFrame_Application_Modules();
            
            // Store data in cache file
            PHPFrame_Utils_Filesystem::write($this->_cache_file, serialize($this->_array));
        }
    }
    
    /**
     * Get Instance
     * 
     * @static
     * @access    public
     * @return     PHPFrame_Registry
     * @since    1.0
     */
    public static function getInstance() 
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self;
        }
        
        return self::$_instance;
    }
    
    /**
     * Get an application registry variable
     * 
     * @access    public
     * @param    string    $key
     * @param    mixed    $default_value
     * @return    mixed
     * @since    1.0
     */
    public function get($key, $default_value=null) 
    {
        if (!isset($this->_array[$key]) && !is_null($default_value)) {
            $this->_array[$key] = $default_value;
        }
        
        return $this->_array[$key];
    }
    
    /**
     * Set a application registry variable
     * 
     * @access    public
     * @param    string    $key
     * @param    mixed    $value
     * @return    void
     * @since    1.0
     */
    public function set($key, $value) 
    {
        if (array_key_exists($key, $this->_readonly)) {
            throw new PHPFrame_Exception("Tried to set a read-only key (".$key.") in Application Registry.");
        }
        
        $this->_array[$key] = $value;
    }
    
    public function getPermissions() 
    {
        return $this->_array['permissions'];
    }
    
    public function getComponents() 
    {
        return $this->_array['components'];
    }
    
    public function getModules() 
    {
        return $this->_array['modules'];
    }
}
