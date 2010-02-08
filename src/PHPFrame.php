<?php
/**
 * PHPFrame.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   PHPFrame
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * Set convenience DS constant (directory separator depends on server operating 
 * system).
 */
define("DS", DIRECTORY_SEPARATOR);

/**
 * Register autoload function
 */
spl_autoload_register(array("PHPFrame", "autoload"));

/**
 * This class encapsulates information about the installed version of the 
 * framework.
 * 
 * @category PHPFrame
 * @package  PHPFrame
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since    1.0
 */
class PHPFrame
{
    /**
     * The PHPFrame API version
     * 
     * @var string
     */
    const API_VERSION = "##API_VERSION##";
    /**
     * The PHPFrame API stability
     * 
     * @var string
     */
    const API_STABILITY = "##API_STABILITY##";
    /**
     * The PHPFrame release version
     * 
     * @var string
     */
    const RELEASE_VERSION = "##RELEASE_VERSION##";
    /**
     * The PHPFrame release stability
     * 
     * @var string
     */
    const RELEASE_STABILITY = "##RELEASE_STABILITY##";
    /**
     * The build label
     * 
     * @var string
     */
    const BUILD_LABEL = "##BUILD_LABEL##";
    /**
     * The build date
     * 
     * @var string
     */
    const BUILD_DATE = "##BUILD_DATE##";
    /**
     * Core subpackages
     * 
     * @var array
     */
    private static $_subpackages = array(
        "Application", 
        "Base", 
        "Client", 
        "Config", 
        "Database", 
        "Debug", 
        "Document",
        "Documentor",
        "Exception",
        "Ext",
        "Filter",
        "HTTP",
        "Mail",
        "Mapper",
        "MVC",
        "Plugins",
        "Registry",
        "UI",
        "User",
        "Utils"
    );
    /**
     * Absolute path to PHPFrame src dir
     * 
     * @var string
     */
    private static $_src_dir;
    /**
     * Absolute path to PHPFrame data dir (normally a subdirectory inside PEARs 
     * data dir).
     * 
     * @var string
     */
    private static $_data_dir;
    /**
     * Boolean indicating whether we are running in test mode. The mock session
     * class will be used instead of a real session.
     * 
     * @var bool
     */
    private static $_test_mode = false;
    
    /**
     * Constructor
     * 
     * We declare an empty private constructor to ensure this class is not 
     * instantiated. All methods in this class are declared static.
     * 
     * @return void
     * @since  1.0
     */
    private function __construct()
    {
        //...
    }
    
    /**
     * Autoload magic method
     * 
     * This method is automatically called in case you are trying to use a 
     * class/interface which hasn't been defined yet. By calling this function 
     * the scripting engine is given a last chance to load the class before 
     * PHP fails with an error. 
     * 
     * @param string $class_name The class name to load.
     * 
     * @return void
     * @since  1.0
     */
    public static function autoload($class_name)
    {
        $file_path = "";
        
        // Load core PHPFrame classes
        if (preg_match('/^PHPFrame_([a-zA-Z0-9]+)/', $class_name, $matches)) {
            $file_path = $matches[1].".php";
            @include $file_path;
            return;
        }
        
        // Load PEAR dependencies
        $pear_packages = array(
            "PEAR", 
            "XML", 
            "Console", 
            "HTTP", 
            "Archive"
        );
        
        foreach ($pear_packages as $package) {
            if (!preg_match('/^'.$package.'_/', $class_name)) {
                continue;
            }
            
            $pattern = '/_([a-zA-Z0-9]+)/';
            if (preg_match_all($pattern, $class_name, $matches)) {
                $file_path = $package.DS.implode(DS, $matches[1]).".php";
                @include $file_path;
                return;
            }
        }
        
        // Load PHPMailer
        $lib_dir = self::$_data_dir.DS."lib";
        
        if ($class_name == "PHPMailer") {
            $file_path = "phpmailer".DS."phpmailer.php";
            include $lib_dir.DS.$file_path;
            return;
        }
    }
    
    /**
     * Get PHPFrame version
     * 
     * @return string
     * @since  1.0
     */
    public static function version() 
    {
        $str  = "PHPFrame ";
        $str .= self::RELEASE_VERSION." ".self::RELEASE_STABILITY;
        $str .= " (".self::BUILD_LABEL.": ".self::BUILD_DATE.") ";
        $str .= "\nAPI version: ";
        $str .= self::API_VERSION." ".self::API_STABILITY;
        $str .= "\nCopyright (c) 2009 The PHPFrame Group";
        
        return $str;
    }
    
    /**
     * Get session object
     * 
     * @return PHPFrame_SessionRegistry
     * @since  1.0
     */
    public static function getSession() 
    {
        if (self::isTestMode()) {
            return PHPFrame_MockSessionRegistry::getInstance();
        }
        
        return PHPFrame_SessionRegistry::getInstance();
    }
    
    /**
     * Boot up the PHPFrame framework
     * 
     * @return void
     * @since  1.0
     */
    public static function boot()
    {
        // Set paths to source and data directories
        self::$_src_dir   = PEAR_Config::singleton()->get("php_dir");
        self::$_data_dir  = PEAR_Config::singleton()->get("data_dir");
        self::$_data_dir .= DS."PHPFrame";
        
        // Add core subpackages to include path
        foreach (self::$_subpackages as $subpackage) {
            $subpackage_path = dirname(__FILE__).DS."PHPFrame".DS.$subpackage;
            $include_path    = $subpackage_path.PATH_SEPARATOR;
            $include_path   .= get_include_path();
            set_include_path($include_path);
        }
        
        // Initialise PHPFrame's error and exception handlers.
        PHPFrame_ExceptionHandler::instance();
    }
    
    /**
     * Set the framework to run in test mode
     * 
     * @param bool $bool
     * 
     * @return void
     * @since  1.0
     */
    public static function setTestMode($bool)
    {
        self::$_test_mode = (bool) $bool;
    }
    
    /**
     * Is PHPFrame running in test mode?
     * 
     * @return bool
     * @since  1.0
     */
    public static function isTestMode()
    {
        return (bool) self::$_test_mode;
    }
    
    /**
     * Set PHPFrame's data directory. By default this is a directory called 
     * "data/PHPFrame" under the PEAR shared library dir.
     * 
     * @param string $str The absolute path to PHPFrame's data dir.
     * 
     * @return void
     * @since  1.0
     */
    public static function setDataDir($str)
    {
    	$str = trim((string) $str);
    	
    	if (!is_dir($str) || !is_readable($str)) {
    	    $msg  = "Could not set PHPFrame's data dir. Directory '".$str;
    	    $msg .= "' doesn't exist or is not readable.";
    	    throw new InvalidArgumentException($msg);
    	}
    	
    	self::$_data_dir = $str;
    }
}

// Boot up the Framework!!!
PHPFrame::boot();
