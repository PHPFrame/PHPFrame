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
            $config_dir  = PEAR_Config::singleton()->get("data_dir");
            $config_dir .= DS."PHPFrame".DS."etc";
        }
        
        $config_file = $config_dir.DS."phpframe.ini";
        
        return PHPFrame_Config::instance($config_file);
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
     * @param PHPFrame_DSN $dsn An object of type PHPFrame_DSN 
     *                                   used to get DB connection. This parameter 
     *                                   is optional. If omitted a new DSN object 
     *                                   will be created using the database
     *                                   details provided by the config class. 
     * @param string $db_user            If we specify a DSN object we might also 
     *                                   need to provide a db user in order to 
     *                                   connect to the database server.
     * @param string $db_pass            When both a DSN object and a db user have 
     *                                   been passed we might also need to provide 
     *                                   a password for the db connection.
     * @param PHPFrame_Config $config    A config object to use instead of the 
     *                                   previous.
     * 
     * @static
     * @access public
     * @return PHPFrame_Database
     * @since  1.0
     */
    public static function DB(
        PHPFrame_DSN $dsn=null,
        $db_user=null,
        $db_pass=null
    ) {
        // Set DSN using details from config object
        if (is_null($dsn)) {
            $dsn_concrete_class = "PHPFrame_";
            $dsn_concrete_class .= PHPFrame::Config()->get("db.driver")."DSN";
            
            if ($dsn_concrete_class == "PHPFrame_SQLiteDSN") {
                $db_name     = PHPFRAME_VAR_DIR.DS;
                $db_name    .= PHPFrame::Config()->get("db.name");
            } else {
                $db_name = PHPFrame::Config()->get("db.name");
            }
            
            $dsn = new $dsn_concrete_class(array(
                "db_host" => PHPFrame::Config()->get("db.host"), 
                "db_name" => $db_name
            ));
        } elseif (!$dsn instanceof PHPFrame_DSN) {
            $msg = "Argument \$dsn must be instance of PHPFrame_DSN.";
            throw new InvalidArgumentException($msg);
        }
        
        if (!$dsn instanceof PHPFrame_DSN) {
            $msg = "Could not acquire DSN object to instantiate DB object.";
            throw new RuntimeException($msg);
        }
        
        if (is_null($db_user)) {
            $db_user = PHPFrame::Config()->get("db.user");
        }
        
        if (is_null($db_pass)) {
            $db_pass = PHPFrame::Config()->get("db.pass");
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
        
        // Set profiler milestone
        PHPFrame_Profiler::instance()->addMilestone();
        
        // Initialise app config
        $config = self::Config();
        
        // Load language files
        self::_loadLanguage();
        
        // Initialise phpFame's error and exception handlers.
        $exception_handler = PHPFrame_ExceptionHandler::instance();
        
        // Attach logger observer to exception handler
        $exception_handler->attach(PHPFrame_Logger::instance());
        
        // Attach informer observer to excpetion handler if informer is enabled
        if ($config->get("debug.informer_level") > 0) {
            // Create informer
            $recipients = explode(",", $config->get("debug.informer_recipients"));
            $mailer     = new PHPFrame_Mailer();
            $informer   = new PHPFrame_Informer($recipients, $mailer);
            
            // Attach informer to exception handler
            $exception_handler->attach($informer);
        }
        
        // Set timezone
        date_default_timezone_set($config->get("timezone"));
        
        // Set run level to 1, framework is ready to go!!!
        self::$_run_level = 1;
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
        
        // Fall back to SQLite embedded db if no db enabled in etc/phpframe.ini
        // Otherwise we pass a null dsn to use config settings
        if (!PHPFrame::Config()->get("db.enable")) {
            $msg  = "Tried to mount DB persistence but it is not enabled in ";
            $msg .= "etc/phpframe.ini. Falling back to embedded SQLite3 ";
            $msg .= "database";
            $sysevents = PHPFrame::Session()->getSysevents();
            $sysevents->append($msg, PHPFrame_Subject::EVENT_TYPE_NOTICE);
            
            $dsn_options = array("db_name"=>PHPFRAME_VAR_DIR.DS."data.db");
            $dsn = new PHPFrame_SQLiteDSN($dsn_options);
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
        // load the application language file if any
        if (defined("PHPFRAME_INSTALL_DIR")) {
            $lang_file = PHPFRAME_INSTALL_DIR.DS."src".DS."lang".DS;
            $lang_file .= PHPFrame::Config()->get("default_lang").DS;
            $lang_file .= "global.php";
            
            if (file_exists($lang_file)) {
                require $lang_file;
            } else {
                $msg = 'Could not find language file ('.$lang_file.')';
                throw new RuntimeException($msg);
            }
        }
        
        // Include the PHPFrame framework's language file
        $lang_file = "PHPFrame".DS."Lang";
        $lang_file .= DS.PHPFrame::Config()->get("default_lang").".php";
        
        if (!(require $lang_file)) {
            $msg = 'Could not find language file ('.$lang_file.')';
            throw new RuntimeException($msg);
        }
    }
}

// Boot up the PHPFrame!!!
PHPFrame::Boot();
