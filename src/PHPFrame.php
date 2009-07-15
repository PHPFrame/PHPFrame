<?php
/**
 * PHPFrame/PHPFrame.php
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
    const VERSION='1.0 Alpha';
    /**
     * Run level
     * 
     * @var int
     */
    private static $_run_level=0;
    
    /**
     * Constructor
     * 
     * @return void
     */
    private function __construct() {}
    
    /**
     * Autoload magic method
     * 
     * This method is automatically called in case you are trying to use a class/interface which 
     * hasn't been defined yet. By calling this function the scripting engine is given a last 
     * chance to load the class before PHP fails with an error. 
     * 
     * @param string $class_name The class name to load.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public static function __autoload($class_name) {
        // PHPFrame classes
        if (strpos($class_name, 'PHPFrame') !== false) {
            $array = explode('_', $class_name);
            
            if (sizeof($array) == 4) {
                $file_path = "PHPFrame".DS.$array[1].DS.$array[2].DS.$array[3].".php";
            } elseif (sizeof($array) == 3) {
                $file_path = "PHPFrame".DS.$array[1].DS.$array[2].".php";
            } elseif (sizeof($array) == 2) {
                $file_path = "PHPFrame".DS.$array[1].DS.$array[1].".php";
            }
            
            @include_once $file_path;
        }
    }
    
    /**
     * Get PHPFrame version
     * 
     * @return string
     * @since  1.0
     */
    public static function Version() 
    {
        return self::VERSION;
    }
    
    public function Config()
    {
        if (defined("PHPFRAME_CONFIG_DIR")) {
            $config_file = PHPFRAME_CONFIG_DIR;
        } else {
            require_once "PEAR/Config.php";
			$config_file = PEAR_Config::singleton()->get('data_dir');
        }
        
        $config_file .= DS."config.xml";
        
        return PHPFrame_Config::instance($config_file);
    }
    
    /**
     * Get application registry
     * 
     * @param sring $path The path to the cache directory where to store the app
     *                    registry. If not passed it uses a directory called
     *                    "cache" within the directory specified in PHPFRAME_VAR_DIR
     * 
     * @return PHPFrame_Registry_Application
     * @since  1.0
     */
    public static function AppRegistry($path='') 
    {
        if (empty($path)) {
            $path = PHPFRAME_VAR_DIR.DS."cache";
        }
        
        return PHPFrame_Registry_Application::getInstance($path);
    }
    
    /**
     * Get session object
     * 
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
     *        		                     used to get DB connection. This parameter 
     *        							 is optional. If omitted a new DSN object 
     *        							 will be created using the database
     *        							 details provided by the config class. 
     * @param string $db_user            If we specify a DSN object we might also 
     *                                   need to provide a db user in order to 
     *                                   connect to the database server.
     * @param string $db_pass            When both a DSN object and a db user have 
     *                                   been passed we might also need to provide 
     *                                   a password for the db connection.
     * @param PHPFrame_Config $config    A config object to use instead of the previous.
     * 
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
            $dsn_concrete_class .= PHPFrame::Config()->get("DB_DRIVER");
            
            $dsn = new $dsn_concrete_class(
                PHPFrame::Config()->get("DB_HOST"), 
                PHPFrame::Config()->get("DB_NAME")
            );
        }
        
        if (is_null($db_user)) {
            $db_user = PHPFrame::Config()->get("DB_USER");
        }
        
        if (is_null($db_pass)) {
            $db_pass = PHPFrame::Config()->get("DB_PASS");
        }
        
        if (!$dsn instanceof PHPFrame_Database_DSN) {
            $msg = "Could not acquire DSN object to instantiate DB object.";
            throw new PHPFrame_Exception($msg);
        }
        
        return PHPFrame_Database::getInstance($dsn, $db_user, $db_pass);
    }
    
    public static function Boot()
    {
        // Set run level to 1 to indicate that PHPFrame is booting...
        self::$_run_level = 1;
        
        // Load language files
        self::_loadLanguage();
        
        // Initialise phpFame's error and exception handlers.
        PHPFrame_Exception_Handler::init();
        
        // Initialise app config
        self::Config();
        
        // Set timezone
        date_default_timezone_set(self::Config()->get("TIMEZONE"));
        
        // Initialise Database
        self::DB();
        
        // Initialise AppRegistry
        self::AppRegistry();
        
        // Get/init session object
        self::Session();
        
        // Set run level to 2, framework is ready to go!!!
        self::$_run_level = 2;
    }
    
    /**
     * Fire up the app
     * 
     * This method instantiates the front controller and runs it.
     * 
     * @return void
     * @since  1.0
     */
    public static function Fire() 
    {
        $frontcontroller = new PHPFrame_Application_FrontController();
        $frontcontroller->run();
    }
    
    public function getRunLevel()
    {
        return self::$_run_level;
    }
    
    /**
     * Load language files
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private static function _loadLanguage()
    {
        // load the application language file if any
        if (defined("PHPFRAME_INSTALL_DIR")) {
            $lang_file = PHPFRAME_INSTALL_DIR.DS."src".DS."lang".DS;
            $lang_file .= PHPFrame::Config()->get("DEFAULT_LANG").".php";
            
            if (file_exists($lang_file)) {
                require_once $lang_file;
            } else {
                throw new PHPFrame_Exception('Could not find language file ('.$lang_file.')');
            }
        }
        
        // Include the PHPFrame framework's language file
        $lang_file = "PHPFrame".DS."Lang".DS.PHPFrame::Config()->get("DEFAULT_LANG").".php";
        if (!(require_once $lang_file)) {
            throw new PHPFrame_Exception('Could not find language file ('.$lang_file.')');
        }
    }
}

// Boot up the PHPFrame!!!
PHPFrame::Boot();
