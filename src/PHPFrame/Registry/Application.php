<?php
/**
 * PHPFrame/Registry/Application.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Registry
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * The Application Registry class produces a singleton object that provides 
 * an application wide scope to be shared by all requests and sessions. 
 * 
 * The application registry is accessed from the PHPFrame facade class as follows:
 * 
 * <code>
 * $session = PHPFrame::AppRegistry();
 * </code>
 * 
 * @category PHPFrame
 * @package  Registry
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see      PHPFrame_Registry
 * @uses     PHPFrame_Application_Permissions, PHPFrame_Application_Libraries, 
 *           PHPFrame_Application_Features, PHPFrame_Exception, 
 *           PHPFrame_Utils_Filesystem
 * @since    1.0
 */
class PHPFrame_Registry_Application extends PHPFrame_Registry
{
    /**
     * Instance of itself in order to implement the singleton pattern
     * 
     * @var object of type PHPFrame_Registry_Application
     */
    private static $_instance = null;
    /**
     * Path to the cache directory in filesystem
     * 
     * @var string
     */
    private $_path = null;
    /**
     * Path to the cache file name in filesystem
     * 
     * @var string
     */
    private $_cache_file = null;
    /**
     * Array containing keys that should be treated as readonly as far as client
     * code is concerned
     * 
     * @var array
     */
    private $_readonly = array("permissions", "features", "libraries");
    /**
     * An array to store application registry data set on runtime
     * 
     * @var array
     */
    private $_data = array();
    /**
     * A boolean to indicate whether the data has changed since it was last 
     * written to file
     * 
     * @var bool
     */
    private $_dirty = false;
    
    /**
     * Constructor
     * 
     * The constructor is declared "protected" to make sure that this class can only
     * be instantiated using the static method getInstance(), serving up always the 
     * same instance that the class stores statically.
     * 
     * Yes, you have guessed right, this class is a "singleton".
     * 
     * @access protected
     * @return void
     * @since  1.0
     */
    protected function __construct() 
    {
        if (!defined("PHPFRAME_TMP_DIR")) {
            $msg  = "Application registry could not be initialised. ";
            $msg .= "PHPFRAME_TMP_DIR constant has not been defined. ";
            $msg .= "Please make sure that you use the application registry from ";
            $msg .= "an application context. Your app will need to define the ";
            $msg .= "PHPFRAME_TMP_DIR constant with a valid path to the directory ";
            $msg .= "where store the app registry's cache.";
            throw new LogicException($msg);
        }
        
        $path = PHPFRAME_TMP_DIR.DS."cache";
        
        // Set path to cache file
        $this->_path = $path;
        $this->_cache_file = "application.registry";
        
        // Read data from cache
        if (is_file($this->getFilePath())) {
            $serialized_array = file_get_contents($this->getFilePath());
            $this->_data      = unserialize($serialized_array);
        }
        else {
            // Rebuild app registry
            $permissions = new PHPFrame_Application_Permissions();
            $libs        = new PHPFrame_Application_Libraries();
            $features    = new PHPFrame_Application_Features();
            
            // Store objects in App Regsitry
            $this->set("permissions", $permissions);
            $this->set("libraries", $libs);
            $this->set("features", $features); 
        }
    }
    
    /**
     * Destructor
     * 
     * The destructor method will be called as soon as all references to a 
     * particular object are removed or when the object is explicitly destroyed or 
     * in any order in shutdown sequence.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __destruct()
    {
        if ($this->isDirty()) {
            try {
                // Write data to file
                $this->_writeToFile();
            } catch (Exception $e) {
                trigger_error($e->getMessage());
                exit;
            }
        }
    }
    
    public function __sleep()
    {
        $this->_dirty = null;
    }
    
    public function __wakeup()
    {
        $this->_dirty = false;
    }
    
    /**
     * Get Instance
     * 
     * @static
     * @access public
     * @return PHPFrame_Registry
     * @since  1.0
     */
    public static function getInstance() 
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    /**
     * Get an application registry variable
     * 
     * @param string $key
     * @param mixed  $default_value
     * 
     * @access public
     * @return mixed
     * @since  1.0
     */
    public function get($key, $default_value=null) 
    {
        // Set default value if appropriate
        if (!isset($this->_data[$key]) && !is_null($default_value)) {
            $this->_data[$key] = $default_value;
            
            // Mark data as dirty
            $this->markDirty();
        }
        
        // Return null if index is not defined
        if (!isset($this->_data[$key])) {
            return null;
        }
        
        return $this->_data[$key];
    }
    
    /**
     * Set an application registry variable
     * 
     * @param string $key
     * @param mixed  $value
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function set($key, $value) 
    {
        if (array_key_exists($key, $this->_readonly)) {
            $msg = "Tried to set a read-only key (";
            $msg .= $key.") in Application Registry.";
            throw new PHPFrame_Exception($msg);
        }
        
        $this->_data[$key] = $value;
        
        // Mark data as dirty
        $this->markDirty();
    }
    
    /**
     * Get full path to cache file
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getFilePath()
    {
        return $this->_path.DS.$this->_cache_file;
    }
    
    /**
     * Get Permissions object
     * 
     * @access public
     * @return PHPFrame_Application_Permissions
     * @since  1.0
     */
    public function getPermissions() 
    {
        return $this->_data['permissions'];
    }
    
    /**
     * Get Features
     * 
     * @access public
     * @return PHPFrame_Application_Features
     * @since  1.0
     */
    public function getFeatures() 
    {
        return $this->_data['features'];
    }
    
    /**
     * Get Libraries
     * 
     * @access public
     * @return PHPFrame_Application_Libraries
     * @since  1.0
     */
    public function getLibraries() 
    {
        return $this->_data['libraries'];
    }
    
    /**
     * Mark the application data as dirty (it needs writting to file)
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    public function markDirty()
    {
        $this->_dirty = true;
    }
    
    /**
     * Is the application registry data dirty?
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function isDirty()
    {
        return $this->_dirty;
    }
    
    /**
     * Write application registry to file
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function _writeToFile()
    {
        // Ensure that cache dir is writable
        PHPFrame_Utils_Filesystem::ensureWritableDir($this->_path);
        
        // Store data in cache file
        $data = serialize($this->_data);
        PHPFrame_Utils_Filesystem::write($this->getFilePath(), $data);
    }
}
