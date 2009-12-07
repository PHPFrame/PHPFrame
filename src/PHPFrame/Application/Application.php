<?php
/**
 * PHPFrame/Application/Application.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Application
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * The Application class encapsulates all objects that make up the structure
 * of an MVC application.
 * 
 * This class is composed mainly of other objects (config, db, features, 
 * logger, ...) and caches application wide data in a file based "Registry".
 * 
 * The Application class is responsible for initialising an app and dispatching
 * requests and thus processing input and output to the application as a whole.
 * 
 * @category PHPFrame
 * @package  Application
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since    1.0
 */
class PHPFrame_Application
{
    /**
     * Absolute path to application in filesystem
     * 
     * @var string
     */
    private $_install_dir;
    /**
     * Absolute path to "config" directory. By default this will be a 
     * subdirectory called "etc" inside install_dir.
     * 
     * @var string
     */
    private $_config_dir;
    /**
     * Absolute path to "variable" directory. By default this will be a 
     * subdirectory called "var" inside install_dir. This is where the app will
     * store files (except for configuration and temporary files).
     * 
     * @var string
     */
    private $_var_dir;
    /**
     * Absolute path to "temporary" directory. By default this will be a 
     * subdirectory called "tmp" inside install_dir.
     * 
     * @var string
     */
    private $_tmp_dir;
    /**
     * Configuration object
     * 
     * @var PHPFrame_Config
     */
    private $_config;
    /**
     * Registry object used to cache application wide objects
     * 
     * @var PHPFrame_FileRegistry
     */
    private $_registry;
    /**
     * The Request object the application will handle
     * 
     * @var PHPFrame_Request
     */
    private $_request;
    /**
     * The Response object used for the application output
     * 
     * @var PHPFrame_Response
     */
    private $_response;
    /**
     * An instance of PluginHandler that the application will use to provide
     * hooks for plugins.
     * 
     * @var PHPFrame_PluginHandler
     */
    private $_plugin_handler;
    /**
     * Instance of MVC factory used to server up userland controllers, models, 
     * helpers and so on.
     * 
     * @var PHPFrame_MVCFactory
     */
    private $_mvc_factory;
    /**
     * A prefix used for MVC userland classes. Default value is empty. This 
     * affects controllers, models, helpers and language classes.
     * 
     * @var string
     */
    private $_class_prefix = "";
    
    /**
     * Constructor
     * 
     * @param array $options [Optional] An associative array with the following
     *                                  keys:
     *                                  - install_dir [Required]
     *                                  - config_dir  [Optional]
     *                                  - var_dir     [Optional]
     *                                  - tmp_dir     [Optional]
     * 
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        if (!isset($options["install_dir"])) {
            $msg  = "Otions array passed to ".get_class($this)."::";
            $msg .= __FUNCTION__."() must contain 'install_dir' key.";
            throw new InvalidArgumentException($msg);
        }
        
        if (!is_string($options["install_dir"])) {
            $msg  = "'install_dir' option passed to ".get_class($this);
            $msg .= " must be of type string and value passed of type '";
            $msg .= gettype($options["install_dir"])."'.";
            throw new InvalidArgumentException($msg);
        }
        
        if (!is_dir($options["install_dir"]) 
            || !is_readable($options["install_dir"])
        ) {
            $msg = "Could not read directory ".$options["install_dir"];
            throw new RuntimeException($msg);
        }
        
        $this->_install_dir = $options["install_dir"];
        
        $option_keys = array(
            "config_dir" => "etc", 
            "var_dir"    => "var", 
            "tmp_dir"    => "tmp"
        );
        
        foreach ($option_keys as $key=>$value) {
            $prop_name = "_".$key;
            if (isset($options[$key]) && !is_null($options[$key])) {
                $this->$prop_name = $options[$key];
            } else {
                $this->$prop_name = $this->_install_dir.DS.$value;
            }
            
            if ((!is_dir($this->$prop_name) && !mkdir($this->$prop_name))
                || !is_writable($this->$prop_name)
            ) {
                $msg = "Directory ".$this->$prop_name." is not writable.";
                throw new RuntimeException($msg);
            }
        }
        
        // Throw exception if config file doesn't exist
        $config_file = $this->_config_dir.DS."phpframe.ini";
        if (!is_file($config_file)) {
            $msg = "Config file ".$config_file." not found.";
            throw new RuntimeException($msg);
        }
        
        // Acquire config object and cache it
        $this->setConfig(new PHPFrame_Config($config_file));
        
        // Acquire and store instance of MVC Factory class
        $this->setMVCFactory(new PHPFrame_MVCFactory($this));
        
        // Set profiler milestone
        $profiler = $this->getProfiler();
        if ($profiler instanceof PHPFrame_Profiler) {
            $profiler->addMilestone();
        }
    }
    
    /**
     * Magic method to autoload application specific classes
     * 
     * This autoloader is registered in {@link PHPFrame_Application::dispatch()}.
     * 
     * @param string $class_name The name of the class to attempt loading.
     * 
     * @return void
     * @since  1.0
     */
    public function autoload($class_name)
    {
        $file_path = $this->getInstallDir().DS."src".DS;
        
        // Autoload Controllers, Helpers and Language classes
        $super_classes = array("Controller", "Helper", "Lang");
        foreach ($super_classes as $super_class) {
            if (preg_match('/'.$super_class.'$/', $class_name)) {
                // Set base path for objects of given superclass
                $file_path .= strtolower($super_class);
                break;
            }
        }
        
        // Append lang dir based on config for lang classes
        if ($super_class == "Lang") {
            $file_path .= DS.$this->getConfig()->get("default_lang");
        } else {
            // Append 's' to dir name except for all others
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
        
        // Autoload models
        $models_dir   = $this->_install_dir.DS."src".DS."models";
        $dir_iterator = new RecursiveDirectoryIterator($models_dir);
        $filter       = array("php");
        $iterator     = new RecursiveIteratorIterator(
            $dir_iterator, 
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            //echo $file->getRealPath(); continue;
            if (in_array(end(explode('.', $file->getFileName())), $filter)) {
                $file_name_without_ext = substr(
                    $file->getFileName(), 
                    0, 
                    strpos($file->getFileName(), ".")
                );
                
                if (strtolower($class_name) == $file_name_without_ext) {
                    include_once $file->getRealPath();
                }
            }
        }
    }
    
    /**
     * Get absolute path to installation directory
     * 
     * @return string
     * @since  1.0
     */
    public function getInstallDir()
    {
        return $this->_install_dir;
    }
    
    /**
     * Get Config object
     * 
     * @return PHPFrame_Config
     * @since  1.0
     */
    public function getConfig()
    {
        return $this->_config;
    }
    
    /**
     * Get Logger object
     * 
     * @return PHPFrame_Logger
     * @since  1.0
     */
    public function getLogger()
    {
        if ($this->getConfig()->get("debug.log_level") <= 0) {
            return;
        }
        
        if (is_null($this->getRegistry()->get("logger"))) {
            $logger = new PHPFrame_TextLogger($this->_tmp_dir.DS."app.log");
            $this->setLogger($logger);
        }
        
        return $this->getRegistry()->get("logger");
    }
    
    /**
     * Get Informer object
     * 
     * @return PHPFrame_Informer
     * @since  1.0
     */
    public function getInformer()
    {
        if ($this->getConfig()->get("debug.informer_level") <= 0) {
            return;
        }
        
        if (is_null($this->getRegistry()->get("informer"))) {
            // Create informer
            $recipients = $this->getConfig()->get("debug.informer_recipients");
            $recipients = explode(",", $recipients);
            $mailer     = $this->getMailer();
            
            if (!$mailer instanceof PHPFrame_Mailer) {
                $msg  = "Can not create informer object. No mailer has been ";
                $msg .= "loaded in application. Please check the 'smtp' ";
                $msg .= "section in the config file.";
                throw new LogicException($msg);
            }
            
            $this->setInformer(new PHPFrame_Informer($mailer, $recipients));
        }
        
        return $this->getRegistry()->get("informer");
    }
    
    /**
     * Get Profiler object
     * 
     * @return PHPFrame_Profiler
     * @since  1.0
     */
    public function getProfiler()
    {
        if ($this->getConfig()->get("debug.profiler_enable") != 1) {
            return;
        }
        
        if (is_null($this->getRegistry()->get("profiler"))) {
            $this->setProfiler(new PHPFrame_Profiler());
        }
        
        return $this->getRegistry()->get("profiler");
    }
    
    /**
     * Get Registry object
     * 
     * The registry object is used to cache all application objects that are 
     * shared across the whole app. This registry is itself automatically 
     * cached to file during garbage collection.
     * 
     * @return PHPFrame_FileRegistry
     * @since  1.0
     */
    public function getRegistry()
    {
        if (is_null($this->_registry)) {
            $cache_dir = $this->_tmp_dir.DS."cache";
            PHPFrame_Filesystem::ensureWritableDir($cache_dir);
            
            $cache_file = $cache_dir.DS."app.reg";
            $this->setRegistry(new PHPFrame_FileRegistry($cache_file));
        }
        
        return $this->_registry;
    }
    
    /**
     * Get database object
     * 
     * @param array $options [Optional] An associative array containing the  
     *                       following options: 
     *                         - driver (required)
     *                         - name (required)
     *                         - host
     *                         - user
     *                         - pass
     *                         - mysql_unix_socket
     *                       This parameter is optional. If omitted options
     *                       will be loaded from etc/phpframe.ini.
     * 
     * @return PHPFrame_Database
     * @since  1.0
     */
    public function getDB(array $options=null)
    {
        if (is_null($options)) {
            $options = $this->getConfig()->getSection("db");
        }
        
        if (!array_key_exists("driver", $options) 
            || !array_key_exists("name", $options)
        ) {
            $msg  = "'driver' and 'name' are required in options array";
            throw new InvalidArgumentException($msg);
        }
        
        // Make absolute path for sqlite db if relative given
        if (
            strtolower($options["driver"]) == "sqlite" 
            && !preg_match('/^\//', $options["name"])
        ) {
            $options["name"] = $this->_var_dir.DS.$options["name"];
        }
        
        return PHPFrame_DatabaseFactory::getDB($options);
    }
    
    /**
     * Get Mailer object
     * 
     * @return PHPFrame_Mailer
     * @since  1.0
     */
    public function getMailer()
    {
        if (is_null($this->getRegistry()->get("mailer"))
            && $this->getConfig()->get("smtp.enable")
        ) {
            $options = $this->getConfig()->getSection("smtp");
            $mailer  = new PHPFrame_Mailer($options);
            $this->setMailer($mailer);
        }
        
        return $this->getRegistry()->get("mailer");
    }
    
    /**
     * Get Permissions object
     * 
     * @return PHPFrame_Permissions
     * @since  1.0
     */
    public function getPermissions()
    {
        if (is_null($this->getRegistry()->get("permissions"))) {
            // Create mapper for ACL objects
            $mapper = new PHPFrame_Mapper(
                "PHPFrame_ACL", 
                $this->_config_dir, 
                "acl"
            );
            
            $this->setPermissions(new PHPFrame_Permissions($mapper));
        }
        
        return $this->_registry->get("permissions");
    }
    
    /**
     * Get Libraries object
     * 
     * @return PHPFrame_Libraries
     * @since  1.0
     */
    public function getLibraries()
    {
        if (is_null($this->getRegistry()->get("libraries"))) {
            // Create mapper for PHPFrame_LibInfo object
            $mapper = new PHPFrame_Mapper(
                "PHPFrame_LibInfo", 
                $this->_config_dir, 
                "lib"
            );
            
            $this->setLibraries(new PHPFrame_Libraries($mapper));
        }
        
        return $this->_registry->get("libraries");
    }
    
    /**
     * Get Features object
     * 
     * @return PHPFrame_Features
     * @since  1.0
     */
    public function getFeatures()
    {
        if (is_null($this->getRegistry()->get("features"))) {
            // Create mapper for PHPFrame_Features object
            $mapper = new PHPFrame_Mapper(
                "PHPFrame_FeatureInfo", 
                $this->_config_dir, 
                "features"
            );
            
            $this->setFeatures(new PHPFrame_Features($mapper));
        }
        
        return $this->_registry->get("features");
    }
    
    /**
     * Get Plugins object
     * 
     * @return PHPFrame_Plugins
     * @since  1.0
     */
    public function getPlugins()
    {
        if (is_null($this->getRegistry()->get("plugins"))) {
            // Create mapper for PHPFrame_Plugins object
            $mapper = new PHPFrame_Mapper(
                "PHPFrame_PluginInfo", 
                $this->_config_dir, 
                "plugins"
            );
            
            $this->setPlugins(new PHPFrame_Plugins($mapper));
        }
        
        return $this->_registry->get("plugins");
    }
    
    /**
     * Get Request object
     * 
     * @return PHPFrame_Request
     * @since  1.0
     */
    public function getRequest()
    {
        if (is_null($this->_request)) {
            // Create new request
            $request = new PHPFrame_Request();
            
            // populate request using client
            PHPFrame::getSession()->getClient()->populateRequest($request);
            
            $this->setRequest($request);
        }
        
        return $this->_request;
    }
    
    /**
     * Get Response object
     * 
     * @return PHPFrame_Response
     * @since  1.0
     */
    public function getResponse()
    {
        if (is_null($this->_response)) {
            // Create new response object
            $response = new PHPFrame_Response();
            $response->setHeader(
                "Content-Language", 
                $this->getConfig()->get("default_lang")
            );
            
            // Prepare response using client
            $client     = PHPFrame::getSession()->getClient();
            $views_path = $this->getInstallDir().DS."src".DS."views";
            $client->prepareResponse($response, $views_path);
            
            $this->setResponse($response);
        }
        
        return $this->_response;
    }
    
    /**
     * Set configuration object
     * 
     * @param PHPFrame_Config $config The new configuration object to use in 
     *                                the application.
     * 
     * @return void
     * @since  1.0
     */
    public function setConfig(PHPFrame_Config $config)
    {
        $this->_config = $config;
        
        // Set timezone
        date_default_timezone_set($config->get("timezone"));
        
        // Set display_exceptions in exception handler
        $display_exceptions = $config->get("debug.display_exceptions");
        PHPFrame_ExceptionHandler::setDisplayExceptions($display_exceptions);
    }
    
    /**
     * Set Registry object
     * 
     * @param PHPFrame_FileRegistry $file_registry A file registry object used 
     *                                             to cache application wide 
     *                                             data to file.
     * 
     * @return void
     * @since  1.0
     */
    public function setRegistry(PHPFrame_FileRegistry $file_registry)
    {
        $this->_registry = $file_registry;
    }
    
    /**
     * Set Logger object
     * 
     * @param PHPFrame_Logger $logger Logger object to be used in application.
     * 
     * @return void
     * @since  1.0
     */
    public function setLogger(PHPFrame_Logger $logger)
    {
        $this->getRegistry()->set("logger", $logger);
        
        // Attach logger observer to exception handler
        PHPFrame_ExceptionHandler::instance()->attach($logger);
    }
    
    /**
     * Set Informer object
     * 
     * @param PHPFrame_Informer $informer Informer object to be used in 
     *                                    application.
     * 
     * @return void
     * @since  1.0
     */
    public function setInformer(PHPFrame_Informer $informer)
    {
        $this->getRegistry()->set("informer", $informer);
        
        // Attach informer to exception handler
        PHPFrame_ExceptionHandler::instance()->attach($informer);
    }
    
    /**
     * Set Profiler object
     * 
     * @param PHPFrame_Profiler $profiler Profiler object to be used in 
     *                                    application.
     * 
     * @return void
     * @since  1.0
     */
    public function setProfiler(PHPFrame_Profiler $profiler)
    {
        $this->getRegistry()->set("profiler", $profiler);
    }
    
    /**
     * Set Database object
     * 
     * @param PHPFrame_Database $db Default database object for application.
     * 
     * @return void
     * @since  1.0
     */
    public function setDB(PHPFrame_Database $db)
    {
        $this->getRegistry()->set("db", $db);
    }
    
    /**
     * Set Mailer object used for outgoing email
     * 
     * @param PHPFrame_Mailer $mailer Mailer object.
     * 
     * @return void
     * @since  1.0
     */
    public function setMailer(PHPFrame_Mailer $mailer)
    {
        $this->getRegistry()->set("mailer", $mailer);
    }
    
    /**
     * Set IMAP object used for incoming email
     * 
     * @param PHPFrame_IMAP $imap IMAP object.
     * 
     * @return void
     * @since  1.0
     */
    public function setIMAP(PHPFrame_IMAP $imap)
    {
        $this->getRegistry()->set("imap", $imap);
    }
    
    /**
     * Set Permissions object
     * 
     * @param PHPFrame_Permissions $permissions Permissions object.
     * 
     * @return void
     * @since  1.0
     */
    public function setPermissions(PHPFrame_Permissions $permissions)
    {
        $this->getRegistry()->set("permissions", $permissions);
    }
    
    /**
     * Set Features object
     * 
     * @param PHPFrame_Features $features Features object.
     * 
     * @return void
     * @since  1.0
     */
    public function setFeatures(PHPFrame_Features $features)
    {
        $this->getRegistry()->set("features", $features);
    }
    
    /**
     * Set Plugins object
     * 
     * @param PHPFrame_Plugins $plugins Plugins object.
     * 
     * @return void
     * @since  1.0
     */
    public function setPlugins(PHPFrame_Plugins $plugins)
    {
        $this->getRegistry()->set("plugins", $plugins);
    }
    
    /**
     * Set Libraries object
     * 
     * @param PHPFrame_Libraries $libraries Libraries object.
     * 
     * @return void
     * @since  1.0
     */
    public function setLibraries(PHPFrame_Libraries $libraries)
    {
        $this->getRegistry()->set("libraries", $libraries);
    }
    
    /**
     * Set Request object
     * 
     * @param PHPFrame_Request $request Request object.
     * 
     * @return void
     * @since  1.0
     */
    public function setRequest(PHPFrame_Request $request)
    {
        $this->_request = $request;
    }
    
    /**
     * Set Response object
     * 
     * @param PHPFrame_Response $response Response object.
     * 
     * @return void
     * @since  1.0
     */
    public function setResponse(PHPFrame_Response $response)
    {
        $this->_response = $response;
    }
    
    /**
     * Get reference to MVC factory object.
     * 
     * @return PHPFrame_MVCFactory
     * @since  1.0
     */
    public function getMVCFactory()
    {
    	return $this->_mvc_factory;
    }
    
    /**
     * Set MVC factory object.
     * 
     * @param PHPFrame_MVCFactory $mvc_factory Reference to PHPFrame_MVCFactory
     *                                         object.
     *                                         
     * @return void
     * @since  1.0
     */
    public function setMVCFactory(PHPFrame_MVCFactory $mvc_factory)
    {
    	$this->_mvc_factory = $mvc_factory;
    }
    
    /**
     * Get the userland class prefix.
     * 
     * @return string
     * @since  1.0
     */
    public function getClassPrefix()
    {
    	return $this->_class_prefix;
    }
    
    /**
     * Set the userland class prefix.
     * 
     * @param string $str The new class suffix.
     * 
     * @return void
     * @since  1.0
     */
    public function setClassPrefix($str)
    {
    	$this->_class_prefix = trim((string) $str);
    }
    
    /**
     * Dispatch request
     * 
     * @param PHPFrame_Request $request [Optional] If omitted a new request 
     *                                  object will be created using the data 
     *                                  provided by the session client.
     * 
     * @return void
     * @since  1.0
     */
    public function dispatch(PHPFrame_Request $request=null)
    {
        /**
         * Register MVC autoload function
         */
        spl_autoload_register(array($this, "autoload"));
        
        // If no request is passed we try to use request object cached in app
        // or a new request is created using the session's client
        if (is_null($request)) {
            $request = $this->getRequest();
        } else {
            $this->setRequest($request);
        }
        
        // Acquire instance of Plugin Handler
        $this->_plugin_handler = new PHPFrame_PluginHandler($this);
        
        // Register installed plugins with plugin handler
        foreach ($this->getPlugins() as $plugin) {
            if ($plugin->isEnabled()) {
                $plugin_name = $plugin->getName();
                $this->_plugin_handler->registerPlugin(new $plugin_name($this));
            }
        }
        
        // Invoke route startup hook before request object is initialised
        $this->_plugin_handler->handle("routeStartup");
        
        // If no controller has been set we use de default value provided in 
        // etc/phpframe.ini
        $controller_name = $request->getControllerName();
        if (is_null($controller_name) || empty($controller_name)) {
            $def_controller = $this->getConfig()->get("default_controller");
            $request->setControllerName($def_controller);
        }
        
        // Invoke route shutdown hook
        $this->_plugin_handler->handle("routeShutdown");
        
        // Invoke dispatchLoopStartup hook
        $this->_plugin_handler->handle("dispatchLoopStartup");
        
        while (!$request->isDispatched()) {
            // Set request as dispatched
            $request->setDispatched(true);
            
            // Invoke preDispatch hook for every iteration of the dispatch loop
            $this->_plugin_handler->handle("preDispatch");
            
            // If any plugin set dispatched to false we start a new iteration
            if (!$request->isDispatched()) {
                $request->setDispatched(true);
                continue;
            }
            
            // Get requested controller name
            $controller_name = $request->getControllerName();
            
            // Create the action controller
            $mvc_factory = $this->getMVCFactory();
            $controller  = $mvc_factory->getActionController($controller_name);
            
            // Attach observers to the action controller
            $controller->attach(PHPFrame::getSession()->getSysevents());
            
            // Execute the action in the given controller
            $controller->execute($this);
            
            // Invoke postDispatch hook for every iteration of the dispatch loop
            $this->_plugin_handler->handle("postDispatch");
        }
        
        // Invoke dispatchLoopShutdown hook
        $this->_plugin_handler->handle("dispatchLoopShutdown");
        
        $response = $this->getResponse();
        
        // Invoke dispatchLoopShutdown hook
        $this->_plugin_handler->handle("preApplyTheme");
        
        // Apply theme if needed
        $document = $response->getDocument();
        if ($document instanceof PHPFrame_HTMLDocument) {
        	if (!$request->isAJAX()) {
        	    $theme       = $this->getConfig()->get("theme");
                $base_url    = $this->getConfig()->get("base_url");
                $theme_url   = $base_url."themes/".$theme;
                $theme_path  = $this->getInstallDir().DS."public".DS."themes";
                $theme_path .= DS.$theme.DS."index.php";
                $document->applyTheme($theme_url, $theme_path, $this);
        	} else {
                // Append system events when no theme
                $sysevents = PHPFrame::getSession()->getSysevents();
                $sysevents = $response->getRenderer()->render($sysevents);
                $document->prependBody($sysevents);
                
                // Set "body only" mode for AJAX requests when HTML document
                $document->setBodyOnly(true);
        	}
        }
        
        // Invoke dispatchLoopShutdown hook
        $this->_plugin_handler->handle("postApplyTheme");
        
        // If not in quiet mode, send response back to the client
        if (!$request->isQuiet()) {
            $response->send();
        }
        
        // If outfile is defined we write the response to file
        $outfile = $request->getOutfile();
        if (!empty($outfile)) {
            $file_obj = new PHPFrame_FileObject($outfile, "w");
            $file_obj->fwrite((string) $response);
        }
        
        // Handle profiler
        $profiler_enable  = $this->getConfig()->get("debug.profiler_enable");
        $profiler_display = $this->getConfig()->get("debug.profiler_display");
        $profiler_outdir  = $this->getConfig()->get("debug.profiler_outdir");
        
        if ($profiler_enable) {
            // Add final milestone
            $this->getProfiler()->addMilestone();
            
            // Get profiler output by casting object to string
            $profiler_out = (string) $this->getProfiler();
            
            // Display output if set in config
            if ($profiler_display) {
                if (PHPFrame::getSession()->getClientName() != "cli") {
                    echo "<pre>";
                }
                
                // Display output
                echo "Profiler Output:\n\n";
                echo $profiler_out;
            }
            
            // Dump profiler output to file if outdir is specified in config
            if (!empty($profiler_outdir)) {
                $profiler_outfile = $profiler_outdir.DS.time().".ppo";
                $file_obj = new PHPFrame_FileObject($profiler_outfile, "w");
                $file_obj->fwrite($profiler_out);
            }
        }
    }
}
