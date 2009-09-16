<?php
/**
 * PHPFrame/Registry/AppRegistry.php
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
 * The application registry is accessed from the PHPFrame facade class as 
 * follows:
 * 
 * <code>
 * $app_registry = PHPFrame::AppRegistry();
 * </code>
 * 
 * @category PHPFrame
 * @package  Registry
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see      PHPFrame_Registry
 * @uses     PHPFrame_Permissions, PHPFrame_Libraries, 
 *           PHPFrame_Features, PHPFrame_Filesystem
 * @since    1.0
 */
class PHPFrame_AppRegistry extends PHPFrame_Registry
{
    /**
     * Instance of itself in order to implement the singleton pattern
     * 
     * @var object of type PHPFrame_AppRegistry
     */
    private static $_instance = null;
    /**
     * PHPFrame_FileObject object representing the cache file on disk
     * 
     * @var PHPFrame_FileObject
     */
    private $_file_obj = null;
    /**
     * Array containing keys that should be treated as readonly as far as client
     * code is concerned
     * 
     * @var array
     */
    private $_readonly = array(
        "permissions", 
        "features", 
        "libraries", 
        "plugins"
    );
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
     * The constructor is declared "protected" to make sure that this class can 
     * only be instantiated using the static method getInstance(), serving up 
     * always the same instance that the class stores statically.
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
            $msg .= "Please make sure that you use the application registry ";
            $msg .= "from an application context. Your app will need to ";
            $msg .= "define the PHPFRAME_TMP_DIR constant with a valid path ";
            $msg .= "to the directory where store the app registry's cache.";
            throw new LogicException($msg);
        }
        
        $cache_dir = PHPFRAME_TMP_DIR.DS."cache";
        
        PHPFrame_Filesystem::ensureWritableDir($cache_dir);
        
        $cache_file = $cache_dir.DS."application.registry";
        
        // Read data from cache
        if (is_file($cache_file)) {
            // Open cache file in read/write mode
            $this->_file_obj = new PHPFrame_FileObject($cache_file, "r+");
            // Load data from cache file
            $this->_data = unserialize($this->_file_obj->getFileContents());
        } else {
            // Open cache file in write mode
            $this->_file_obj = new PHPFrame_FileObject($cache_file, "w");
            
            // Rebuild app registry
            $this->set("permissions", new PHPFrame_Permissions());
            $this->set("libraries", new PHPFrame_Libraries());
            $this->set("features", new PHPFrame_Features());
            $this->set("plugins", new PHPFrame_Plugins());
        }
    }
    
    /**
     * Destructor
     * 
     * The destructor method will be called as soon as all references to a 
     * particular object are removed or when the object is explicitly destroyed 
     * or in any order in shutdown sequence.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __destruct()
    {
        if ($this->isDirty()) {
            $this->_file_obj->fwrite(serialize($this->_data));
        }
    }
    
    /**
     * Get Instance
     * 
     * @static
     * @access public
     * @return PHPFrame_AppRegistry
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
     * Implementation of IteratorAggregate interface
     * 
     * @access public
     * @return ArrayIterator
     * @since  1.0
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_data);
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
            throw new RuntimeException($msg);
        }
        
        $this->_data[$key] = $value;
        
        // Mark data as dirty
        $this->markDirty();
    }
    
    /**
     * Get Permissions object
     * 
     * @access public
     * @return PHPFrame_Permissions
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
     * @return PHPFrame_Features
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
     * @return PHPFrame_Libraries
     * @since  1.0
     */
    public function getLibraries() 
    {
        return $this->_data['libraries'];
    }
    
    /**
     * Get Plugins
     * 
     * @access public
     * @return PHPFrame_Features
     * @since  1.0
     */
    public function getPlugins() 
    {
        return $this->_data['plugins'];
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
}
