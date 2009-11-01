<?php
/**
 * PHPFrame.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   PHPFrame
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Set convenience DS constant (directory separator depends on server operating 
 * system).
 */
define("DS", DIRECTORY_SEPARATOR);

/**
 * Register autoload function
 */
spl_autoload_register(array("PHPFrame", "__autoload"));

/**
 * This class provides a number of static methods that serve as a simplified
 * interface or facade to PHPFrame's "global" objects, such as the registries 
 * ({@link PHPFrame::Request()}, {@link PHPFrame::Session()} and  
 * {@link PHPFrame::AppRegistry()}), the config ({@link PHPFrame::AppRegistry()}) 
 * and actions involving the state of the framework.
 * 
 * It also provides information about the installed PHPFrame version.
 * 
 * @category PHPFrame
 * @package  PHPFrame
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
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
        "FileSystem",
        "Filter",
        "HTTP",
        "Lang",
        "Mail",
        "Mapper",
        "MVC",
        "Plugins",
        "Registry",
        "VersionControl",
        "UI",
        "User",
        "Utils"
    );
    /**
     * Run level
     * 
     * @var int
     */
    private static $_run_level = 0;
    private static $_app;
    
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
    private function __construct()
    {
        
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
     * @static
     * @access public
     * @return void
     * @since  1.0
     */
    public static function __autoload($class_name)
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
        
        // Load core libraries
        if ($class_name == "InputFilter") {
            $file_path  = PEAR_Config::singleton()->get("data_dir");
            $file_path .= DS."PHPFrame".DS."lib".DS."phpinputfilter".DS;
            $file_path .= "inputfilter.php";
            @include $file_path;
            return;
        } elseif ($class_name == "PHPMailer") {
            $file_path  = PEAR_Config::singleton()->get("data_dir"); 
            $file_path .= DS."PHPFrame".DS."lib".DS."phpmailer".DS."phpmailer.php";
            @include $file_path;
            return;
        } elseif ($class_name == "VCARD") {
            $file_path  = PEAR_Config::singleton()->get("data_dir"); 
            $file_path .= DS."PHPFrame".DS."lib".DS."vcard".DS."vcardclass.inc";
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
        $str  = "PHPFrame ";
        $str .= self::RELEASE_VERSION." ".self::RELEASE_STABILITY;
        $str .= " (".self::BUILD_LABEL.": ".self::BUILD_DATE.") ";
        $str .= "\nAPI version: ";
        $str .= self::API_VERSION." ".self::API_STABILITY;
        $str .= "\nCopyright (c) 2008-2009 E-noise.com Limited";
        
        return $str;
    }
    
    /**
     * Get application registry
     * 
     * @static
     * @access public
     * @return PHPFrame_AppRegistry
     * @since  1.0
     */
    public static function AppRegistry() 
    {
        if (self::getRunLevel() < 2) {
            $msg  = "It looks like you are trying to access an app registry but no";
            $msg .= " app has been initialised.";
            $msg .= " Please call PHPFrame::Env() before trying to access the";
            $msg .= " application registry.";
            throw new LogicException($msg);
        }
        
        return PHPFrame_AppRegistry::getInstance();
    }
    
    /**
     * Get session object
     * 
     * @static
     * @access public
     * @return PHPFrame_SessionRegistry
     * @since  1.0
     */
    public static function Session() 
    {
        if (self::getRunLevel() < 2) {
            $msg  = "It looks like you are trying to access the session registry";
            $msg .= " but no app has been initialised.";
            $msg .= " Please call PHPFrame::Env() before trying to access the";
            $msg .= " session registry.";
            throw new LogicException($msg);
        }
        
        return PHPFrame_SessionRegistry::getInstance();
    }
    
    /**
     * Request Registry
     * 
     * @static
     * @access public
     * @return PHPFrame_RequestRegistry
     * @since  1.0
     */
    public static function Request() 
    {
        if (self::getRunLevel() < 2) {
            $msg  = "It looks like you are trying to access the request registry";
            $msg .= " but no app has been initialised.";
            $msg .= " Please call PHPFrame::Env() before trying to access the";
            $msg .= " request registry.";
            throw new LogicException($msg);
        }
        
        return PHPFrame_RequestRegistry::getInstance();
    }
    
    /**
     * Get response object
     * 
     * @static
     * @access public
     * @return PHPFrame_Response
     * @since  1.0
     */
    public static function Response()
    {
        return PHPFrame_Response::getInstance();
    }
    
    /**
     * Get database object
     * 
     * @param array $options [Optional] An associative array containing the  
     *                       following options: 
     *                         - db.driver (required)
     *                         - db.name (required)
     *                         - db.host
     *                         - db.user
     *                         - db.pass
     *                         - db.mysql_unix_socket
     *                       This parameter is optional. If omitted options
     *                       will be loaded from etc/phpframe.ini.
     * 
     * @static
     * @access public
     * @return PHPFrame_Database
     * @since  1.0
     */
    public static function DB(array $options=null)
    {
        if (is_null($options)) {
            $it = new RegexIterator(
                new IteratorIterator(PHPFrame::Config()), 
                '/^db\./', 
                RegexIterator::MATCH, 
                RegexIterator::USE_KEY
            );
            $options = iterator_to_array($it);
        }
        
        if (
           !array_key_exists("db.driver", $options) 
           || !array_key_exists("db.name", $options)
        ) {
            $msg  = "If options array is provided db.driver and db.name  are ";
            $msg .= "required";
            throw new InvalidArgumentException($msg);
        }
        
        $dsn = strtolower($options["db.driver"]);
        if ($dsn == "sqlite") {
            $dsn .= ":";
            if (!preg_match('/^\//', $options["db.name"])) {
                $dsn .= PHPFRAME_VAR_DIR.DS;
            }
            $dsn .= $options["db.name"];
        } elseif ($dsn == "mysql") {
            $dsn .= ":dbname=".$options["db.name"];
            if (isset($options["db.host"]) && !empty($options["db.host"])) {
                $dsn .= ";host=".$options["db.host"].";";
            }
            if (
                isset($options["db.mysql_unix_socket"]) 
                && !empty($options["db.mysql_unix_socket"])
            ) {
                $dsn .= ";unix_socket=".$options["db.mysql_unix_socket"];
            } else {
                $dsn .= ";unix_socket=".ini_get('mysql.default_socket');
            }
        } else {
            $msg = "Database driver not supported.";
            throw new Exception($msg);
        }
        
        if (isset($options["db.user"]) && !empty($options["db.user"])) {
            $db_user = $options["db.user"];
        } else {
            $db_user = null;
        }
        
        if (isset($options["db.pass"]) && !empty($options["db.pass"])) {
            $db_pass = $options["db.pass"];
        } else {
            $db_pass = null;
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
        // Add core subpackages to include path
        foreach (self::$_subpackages as $subpackage) {
            $subpackage_path = dirname(__FILE__).DS."PHPFrame".DS.$subpackage;
            set_include_path($subpackage_path.PATH_SEPARATOR.get_include_path());
        }
        
        // Load language files
        //self::_loadLanguage();
        
        // Initialise PHPFrame's error and exception handlers.
        PHPFrame_ExceptionHandler::instance();
        
        // Set run level to 1, framework is ready to go!!!
        self::$_run_level = 1;
    }
    
    public static function setApplication(PHPFrame_Application $app)
    {
        self::$_app = $app;
    }
    
    /**
     * Initialise environment, init app registry, session. Request registry is not 
     * initialised here as this is done in the front controller.
     * 
     * @static
     * @access public
     * @return void
     * @since  1.0
     */
    public static function Env()
    {
        // Set profiler milestone
        PHPFrame_Profiler::instance()->addMilestone();
        
        // Initialise AppRegistry
        PHPFrame_AppRegistry::getInstance();
        
        // Get/init session object
        PHPFrame_SessionRegistry::getInstance();
        
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
        // Set profiler milestone
        PHPFrame_Profiler::instance()->addMilestone();
        
        if (self::$_run_level >= 3) {
            return;
        }
        
        if (self::$_run_level < 2) {
            self::Env();
        }
        
        if (!defined("PHPFRAME_VAR_DIR")) {
            $msg  = "No 'var' directory has been defined for your app. ";
            $msg .= "Please make sure to that your app defines the ";
            $msg .= "PHPFRAME_VAR_DIR constant before mounting.";
            throw new LogicException($msg);
        }
        
        // Fall back to SQLite embedded db if no db enabled in etc/phpframe.ini
        // Otherwise we pass a null dsn to use config settings
        if (!PHPFrame::Config()->get("db.enable")) {
            $msg       = "Tried to mount DB persistence but it is not enabled ";
            $msg      .= "in etc/phpframe.ini. Falling back to embedded ";
            $msg      .= "SQLite3 database";
            $sysevents = PHPFrame::Session()->getSysevents();
            $sysevents->append($msg, PHPFrame_Subject::EVENT_TYPE_NOTICE);
            
            $dsn_options = array("db_name"=>PHPFRAME_VAR_DIR.DS."data.db");
            $dsn         = new PHPFrame_SQLiteDSN($dsn_options);
        } else {
            $dsn = null;
        }
        
        // Initialise Database
        $db = self::DB($dsn);
        if ($db instanceof PHPFrame_Database) {
            // Set run level to 3 to indicate that 
            // persistance layer is mounted...
            self::$_run_level = 3;
        } else {
            throw new RuntimeException("Could not create database object");
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
        
        /**
         * Register MVC autoload function
         */
        spl_autoload_register(array("PHPFrame_MVCFactory", "__autoload"));
        
        $frontcontroller = new PHPFrame_FrontController();
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
        // Include the PHPFrame framework's language file
        $lang_file  = "PHPFrame".DS."Lang";
        $lang_file .= DS.PHPFrame::Config()->get("default_lang").".php";
        
        if (!(include $lang_file)) {
            $msg = 'Could not find language file ('.$lang_file.')';
            throw new RuntimeException($msg);
        }
    }
}

// Boot up the PHPFrame!!!
PHPFrame::Boot();
