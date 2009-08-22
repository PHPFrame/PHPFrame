<?php
/**
 * PHPFrame.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Main
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Set convenience DS constant (directory separator depends on server operating system).
 */
define("DS", DIRECTORY_SEPARATOR);

/**
 * Register autoload function
 */
spl_autoload_register(array("PHPFrame", "__autoload"));

/**
 * PHPFrame Class
 * 
 * This class provides a number of static methods that serve as a simplified
 * interface or facade to the PHPFrame framework.
 * 
 * It also provides information about the installed PHPFrame version.
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Main
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame
{
    /**
     * The PHPFrame version
     * 
     * @var string
     */
    const API_VERSION="1.0";
    /**
     * The PHPFrame version
     * 
     * @var string
     */
    const API_STABILITY="alpha";
    /**
     * The PHPFrame version
     * 
     * @var string
     */
    const RELEASE_VERSION="0.0.4";
    /**
     * The PHPFrame version
     * 
     * @var string
     */
    const RELEASE_STABILITY="alpha";
    /**
     * Run level
     * 
     * @var int
     */
    private static $_run_level=0;
    
    /**
     * Constructor
     * 
     * We declare an empty private constructor to ensure this class is not 
     * instantiated. All methods in this class are declared static.
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function __construct() {}
    
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
     * @static
     * @access public
     * @return void
     * @since  1.0
     * @todo   This method needs to be refactored into three different ones.
     *            One to load the PHPFrame lib, then the custom libs and finally the 
     *         MVC classes.
     */
    public static function __autoload($class_name)
    {
        $file_path = "";
        
        // Load default PEAR classes
        if (preg_match('/^PEAR_([a-zA-Z0-9]+)_?([a-zA-Z0-9]+)?/', $class_name, $matches)) {
            $path = "";
            for ($i=1; $i<count($matches); $i++) {
                $path .= DS.$matches[$i];
            }
            
            require "PEAR".$path.".php";
            return;
        }
        
        // Load PEAR XML tools
        if (preg_match('/^XML_([a-zA-Z]+)/', $class_name, $matches)) {
            require "XML".DS.$matches[1].".php";
            return;
        }
        
        // PHPFrame classes
        if (strpos($class_name, 'PHPFrame') !== false) {
            $array = explode('_', $class_name);
            
            if (sizeof($array) == 4) {
                $file_path = "PHPFrame".DS.$array[1].DS.$array[2].DS.$array[3].".php";
            } elseif (sizeof($array) == 3) {
                $file_path = "PHPFrame".DS.$array[1].DS.$array[2].".php";
            } elseif (sizeof($array) == 2) {
                if ($array[1] == "Lang") {
                    $file_path = "PHPFrame".DS.$array[1].DS;
                    $file_path .= PHPFrame::Config()->get("default_lang").".php";
                } else {
                    $file_path = "PHPFrame".DS.$array[1].DS.$array[1].".php";
                }
            }
            
            // require the file if it exists
            // We do not test to see if the file exists because it is a relative path that
            // depends on the include path. Testing is_file() for the relative path alone 
            // returns false
            @include $file_path;
            return;
        }
        
        // Load core libraries
        if ($class_name == "InputFilter") {
            $file_path = PEAR_Config::singleton()->get("data_dir"); 
            $file_path .= DS."PHPFrame".DS."lib".DS."phpinputfilter".DS."inputfilter.php";
            @include $file_path;
            return;
        } elseif ($class_name == "PHPMailer") {
            $file_path = PEAR_Config::singleton()->get("data_dir"); 
            $file_path .= DS."PHPFrame".DS."lib".DS."phpmailer".DS."phpmailer.php";
            @include $file_path;
            return;
        } elseif ($class_name == "VCARD") {
            $file_path = PEAR_Config::singleton()->get("data_dir"); 
            $file_path .= DS."PHPFrame".DS."lib".DS."vcard".DS."vcardclass.inc";
            @include $file_path;
            return;
        }
        
        // Load custom libraries (if we are in an app)
        if (defined("PHPFRAME_CONFIG_DIR")) {
            $libraries = PHPFrame::AppRegistry()->getLibraries();
            
            foreach ($libraries as $lib) {
                if ($lib->getName() == $class_name) {
                    $file_path = PHPFRAME_INSTALL_DIR.DS."lib".DS.$lib->getPath();
                    
                    // require the file if it exists
                    if (is_file($file_path)) {
                        @include $file_path;
                        return;
                    }
                }
            }
        }
        
        // Load MVC classes
        if (!defined("PHPFRAME_INSTALL_DIR")) {
            return;
        }
        
        $file_path .= PHPFRAME_INSTALL_DIR.DS."src".DS;
        
        // Models...
        if (is_file($file_path.DS."models".DS.$class_name.".php")) {
            @include $file_path.DS."models".DS.$class_name.".php";
            return;
        }
        
        $super_classes = array("Controller", "View", "Helper", "Lang");
        foreach ($super_classes as $super_class) {
            if (preg_match('/'.$super_class.'$/', $class_name)) {
                break;
            }
        }
        
        // Set base path for objects of given superclass
        $file_path .= strtolower($super_class);
        
        // Append lang dir for lang classes
        if ($super_class == "Lang") {
            $file_path .= DS.PHPFrame::Config()->get("default_lang");
        // Append 's' to dir name except for all others
        } else {
            $file_path .= "s";
            
        }
        
        // Remove superclass name from class name
        $class_name = str_replace($super_class, "", $class_name);
            
        // Build dir path by breaking camel case class name
        $pattern = '/[A-Z]{1}[a-zA-Z0-9]+/';
        $matches = array();
        preg_match_all($pattern, ucfirst($class_name), $matches);
        if (isset($matches[0]) && is_array($matches[0])) {
            $file_path .= DS.strtolower(implode(DS, $matches[0]));
        }
    
        // Append file extension
        $file_path .= ".php";
        
        // require the file if it exists
        if (is_file($file_path)) {
            @include $file_path;
            return;
        }
    }
    
    /**
     * Get PHPFrame version
     * 
     * @static
     * @access public
     * @return string
     * @since  1.0
     */
    public static function Version() 
    {
        $str = "PHPFrame ";
        $str .= "\nRelease version: ";
        $str .= self::RELEASE_VERSION." ".self::RELEASE_STABILITY;
        $str .= "\nAPI version: ";
        $str .= self::API_VERSION." ".self::API_STABILITY;
        
        return $str;
    }
    
    /**
     * Get global configuration object
     * 
     * @static
     * @access public
     * @return PHPFrame_Config
     * @since  1.0
     */
    public static function Config()
    {
        // If we are in an app we use the app's config
        if (defined("PHPFRAME_CONFIG_DIR")) {
            $config_dir = PHPFRAME_CONFIG_DIR;
        // Otherwise we use the system wide default config
        } else {
            $config_dir = PEAR_Config::singleton()->get("data_dir");
            $config_dir .= DS."PHPFrame".DS."etc";
        }
        
        $config_file = $config_dir.DS."phpframe.ini";
        
        return PHPFrame_Config::instance($config_file);
    }
    
    /**
     * Get application registry
     * 
     * @param sring $path The path to the cache directory where to store the app
     *                    registry. If not passed it uses a directory called
     *                    "cache" within the directory specified in PHPFRAME_TMP_DIR
     * 
     * @static
     * @access public
     * @return PHPFrame_Registry_Application
     * @since  1.0
     */
    public static function AppRegistry($path='') 
    {
        if (empty($path)) {
            $path = PHPFRAME_TMP_DIR.DS."cache";
        }
        
        return PHPFrame_Registry_Application::getInstance($path);
    }
    
    /**
     * Get session object
     * 
     * @static
     * @access public
     * @return PHPFrame_Registry_Session
     * @since  1.0
     */
    public static function Session() 
    {
        return PHPFrame_Registry_Session::getInstance();
    }
    
    /**
     * Request Registry
     * 
     * @static
     * @access public
     * @return PHPFrame_Registry_Request
     * @since  1.0
     */
    public static function Request() 
    {
        return PHPFrame_Registry_Request::getInstance();
    }
    
    /**
     * Get response object
     * 
     * @static
     * @access public
     * @return PHPFrame_Application_Response
     * @since  1.0
     */
    public static function Response()
    {
        return PHPFrame_Application_Response::getInstance();
    }
    
    /**
     * Get database object
     * 
     * @param PHPFrame_Database_DSN $dsn An object of type PHPFrame_Database_DSN 
     *                                     used to get DB connection. This parameter 
     *                                     is optional. If omitted a new DSN object 
     *                                     will be created using the database
     *                                     details provided by the config class. 
     * @param string $db_user            If we specify a DSN object we might also 
     *                                   need to provide a db user in order to 
     *                                   connect to the database server.
     * @param string $db_pass            When both a DSN object and a db user have 
     *                                   been passed we might also need to provide 
     *                                   a password for the db connection.
     * @param PHPFrame_Config $config    A config object to use instead of the previous.
     * 
     * @static
     * @access public
     * @return PHPFrame_Database
     * @since  1.0
     */
    public static function DB(
        PHPFrame_Database_DSN $dsn=null,
        $db_user=null,
        $db_pass=null
    ) {
        // Set DSN using details from config object
        if (!$dsn instanceof PHPFrame_Database_DSN) {
            $dsn_concrete_class = "PHPFrame_Database_DSN_";
            $dsn_concrete_class .= PHPFrame::Config()->get("db.driver");
            
            $dsn = new $dsn_concrete_class(
                PHPFrame::Config()->get("db.host"), 
                PHPFrame::Config()->get("db.name")
            );
        }
        
        if (is_null($db_user)) {
            $db_user = PHPFrame::Config()->get("db.user");
        }
        
        if (is_null($db_pass)) {
            $db_pass = PHPFrame::Config()->get("db.pass");
        }
        
        if (!$dsn instanceof PHPFrame_Database_DSN) {
            $msg = "Could not acquire DSN object to instantiate DB object.";
            throw new PHPFrame_Exception($msg);
        }
        
        return PHPFrame_Database::getInstance($dsn, $db_user, $db_pass);
    }
    
    /**
     * Boot up the PHPFrame framework
     * 
     * @static
     * @access public
     * @return void
     * @since  1.0
     */
    public static function Boot()
    {
        // Load language files
        self::_loadLanguage();
        
        // Initialise phpFame's error and exception handlers.
        PHPFrame_Exception_Handler::init();
        
        // Initialise app config
        self::Config();
        
        // Set timezone
        date_default_timezone_set(self::Config()->get("timezone"));
        
        // Set run level to 1, framework is ready to go!!!
        self::$_run_level = 1;
    }
    
    /**
     * Initialise environment, init app registry and session objects
     * 
     * @static
     * @access public
     * @return void
     * @since  1.0
     */
    public static function Env()
    {
        // Initialise AppRegistry
        self::AppRegistry();
        // Get/init session object
        self::Session();
        
        // Set run level to 2 to indicate that 
        // environment layer is initialised...
        self::$_run_level = 2;
    }
    
    /**
     * Mount persistance layer
     * 
     * @static
     * @access public
     * @return void
     * @since  1.0
     */
    public static function Mount()
    {
        if (self::$_run_level >= 3) {
            return;
        }
        
        if (self::$_run_level < 2) {
            self::Env();
        }
        
        // Initialise Database
        try {
            $db = self::DB();
            if ($db instanceof PHPFrame_Database) {
                // Set run level to 3 to indicate that 
                // persistance layer is mounted...
                self::$_run_level = 3;
            }
        } catch (Exception $e) {
           PHPFrame_Debug_Logger::write("Could not create database object");
        }
    }
    
    /**
     * Fire up the app
     * 
     * This method instantiates the front controller and runs it.
     * 
     * @static
     * @access public
     * @return void
     * @since  1.0
     */
    public static function Fire() 
    {
        // If persistance has not been mounted yet we do so before we
        // run the front controller
        if (self::$_run_level < 2) {
            self::Mount();
        }
        
        $frontcontroller = new PHPFrame_Application_FrontController();
        $frontcontroller->run();
    }
    
    /**
     * Get current run level
     * 
     * @static
     * @access public
     * @return int
     * @since  1.0
     */
    public static function getRunLevel()
    {
        return self::$_run_level;
    }
    
    /**
     * Load language files
     * 
     * @static
     * @access private
     * @return void
     * @since  1.0
     */
    private static function _loadLanguage()
    {
        // load the application language file if any
        if (defined("PHPFRAME_INSTALL_DIR")) {
            $lang_file = PHPFRAME_INSTALL_DIR.DS."src".DS."lang".DS;
            $lang_file .= PHPFrame::Config()->get("default_lang").DS;
            $lang_file .= "global.php";
            
            if (file_exists($lang_file)) {
                require $lang_file;
            } else {
                $msg = 'Could not find language file ('.$lang_file.')';
                throw new PHPFrame_Exception($msg);
            }
        }
        
        // Include the PHPFrame framework's language file
        $lang_file = "PHPFrame".DS."Lang";
        $lang_file .= DS.PHPFrame::Config()->get("default_lang").".php";
        
        if (!(require $lang_file)) {
            $msg = 'Could not find language file ('.$lang_file.')';
            throw new PHPFrame_Exception($msg);
        }
    }
}

// Boot up the PHPFrame!!!
PHPFrame::Boot();
