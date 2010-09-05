<?php
/**
 * PHPFrame/Application/Application.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Application
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
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
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_Application extends PHPFrame_Observer
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
     * Reference to database object.
     *
     * @var PHPFrame_Database
     */
    private $_db;
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
     * @param array $options An associative array with the following keys:
     *                       - install_dir [Required]
     *                       - config_dir  [Optional]
     *                       - var_dir     [Optional]
     *                       - tmp_dir     [Optional]
     *
     * @return void
     * @since  1.0
     */
    public function __construct(array $options)
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

            if ($key != "config_dir"
                && ((!is_dir($this->$prop_name) && !mkdir($this->$prop_name))
                || !is_writable($this->$prop_name))
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
        $this->config(new PHPFrame_Config($config_file));

        // Acquire and store instance of MVC Factory class
        $this->factory(new PHPFrame_MVCFactory($this));

        // Register Application's autoload function
        spl_autoload_register(array($this, "autoload"));

        // Attach observers to Exception handler
        $logger = $this->logger();
        if ($logger instanceof PHPFrame_Logger) {
            PHPFrame_ExceptionHandler::instance()->attach($logger);
        }

        $informer = $this->informer();
        if ($informer instanceof PHPFrame_Logger) {
            PHPFrame_ExceptionHandler::instance()->attach($informer);
        }

        PHPFrame_ExceptionHandler::instance()->attach($this);

        // Acquire instance of Plugin Handler
        $this->_plugin_handler = new PHPFrame_PluginHandler($this);
    }

    /**
     * Application's destructor.
     *
     * @return void
     * @since  1.0
     */
    public function __destruct()
    {
        // Register Application's autoload function
        spl_autoload_unregister(array($this, "autoload"));
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
     * @todo   Need to look at implementation of classPrefix() feature and how
     *         it affects the autoloader.
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
            $file_path .= DS.$this->config()->get("default_lang");
        } else {
            // Append 's' to dir name except for all others
            $file_path .= "s";
        }

        // Remove superclass name from class name
        $class_name = str_replace($super_class, "", $class_name);

        // Remove class prefix if applicable
        //$class_name = str_replace($this->classPrefix(), "", $class_name);

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
        $models_dir = $this->_install_dir.DS."src".DS."models";
        if (is_dir($models_dir)) {
            $dir_iterator = new RecursiveDirectoryIterator($models_dir);
            $filter       = array("php");
            $iterator     = new RecursiveIteratorIterator(
                $dir_iterator,
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $file) {
                if (in_array(end(explode('.', $file->getFileName())), $filter)) {
                    $file_name_no_ext = substr(
                        $file->getFileName(),
                        0,
                        strpos($file->getFileName(), ".")
                    );

                    if (strtolower($class_name) == strtolower($file_name_no_ext)) {
                        include $file->getRealPath();
                        return;
                    }
                }
            }
        }

        // Load libraries
        foreach ($this->libraries() as $lib) {
            $lib_path = $this->getInstallDir().DS."lib";
            $lib_file = $lib_path.DS.$class_name.".php";
            if (is_file($lib_file)) {
                include $lib_file;
                return;
            }
        }
    }

    /**
     * Implementation of PHPFrame_Observer abstract class.
     *
     * @param PHPFrame_Subject $subject Reference to instance of subject being
     *                                  observed.
     *
     * @return void
     * @since  1.0
     */
    protected function doUpdate(PHPFrame_Subject $subject)
    {
        $response = $this->response();
        $request  = $this->request();

        if ($subject instanceof PHPFrame_ExceptionHandler) {
            $exception = $subject->lastException();

            $code = $exception->getCode();
            if (!in_array($code, array(400, 401, 403, 404, 500))) {
                $code = 500;
            }

            if (!$request->param("suppress_response_codes")) {
                $response->statusCode($code);
            } else {
                $response->statusCode(200);
            }

            $content_type = $response->header("Content-Type");
            if (!$request->ajax() && $content_type != "application/json") {
                $response->title("Oooops... an error occurred");
            }

            $display_exceptions = $this->config()->get("debug.display_exceptions");
            if ($display_exceptions) {
                $response->body($exception);
            } else {
                switch ($code) {
                case 400 :
                    $msg = "Bad Request";
                    break;
                case 401 :
                    $msg = "Unauthorised";
                    break;
                case 403 :
                    $msg = "Forbidden";
                    break;
                case 404 :
                    $msg = "Not Found";
                    break;
                case 500 :
                    $msg = "Internal Server Error";
                    break;
                }

                $response->body(new Exception($msg, $code));
            }

            $this->output();

            exit($code);
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
     * Get absolute path to var directory
     *
     * @return string
     * @since  1.0
     */
    public function getVarDir()
    {
        return $this->_var_dir;
    }

    /**
     * Get absolute path to tmp directory
     *
     * @return string
     * @since  1.0
     */
    public function getTmpDir()
    {
        return $this->_tmp_dir;
    }

    /**
     * Get/set configuration object.
     *
     * @param PHPFrame_Config $config [Optional] The new configuration object
     *                                to use in the application.
     *
     * @return PHPFrame_Config
     * @since  1.0
     */
    public function config(PHPFrame_Config $config=null)
    {
        if (!is_null($config)) {
            $this->_setConfig($config);
        }

        return $this->_config;
    }

    /**
     * Get reference to application's session object.
     *
     * @return PHPFrame_SessionRegistry
     * @since  1.0
     */
    public function session()
    {
        return PHPFrame::getSession($this->config()->get("base_url"));
    }

    /**
     * Get reference to session's user object.
     *
     * @return PHPFrame_User
     * @since  1.0
     */
    public function user()
    {
        return $this->session()->getUser();
    }

    /**
     * Get/set Request object.
     *
     * @param PHPFrame_Request $request [Optional] Request object.
     *
     * @return PHPFrame_Request
     * @since  1.0
     */
    public function request(PHPFrame_Request $request=null)
    {
        if (!is_null($request)) {
            $this->_request = $request;

        } elseif (is_null($this->_request)) {
            // Create new request
            $request = new PHPFrame_Request();

            // populate request using client
            $this->session()->getClient()->populateRequest($request);

            $this->_request = $request;
        }

        return $this->_request;
    }

    /**
     * Get/set Response object.
     *
     * @param PHPFrame_Response $response [Optional] Response object.
     *
     * @return PHPFrame_Response
     * @since  1.0
     */
    public function response(PHPFrame_Response $response=null)
    {
        if (!is_null($response)) {
            $this->_response = $response;

        } elseif (is_null($this->_response)) {
            // Create new response object
            $response = new PHPFrame_Response();
            $response->header(
                "Content-Language",
                $this->config()->get("default_lang")
            );

            // Prepare response using client
            $client     = $this->session()->getClient();
            $views_path = $this->getInstallDir().DS."src".DS."views";
            $client->prepareResponse($response, $views_path);

            $this->_response = $response;
        }

        return $this->_response;
    }

    /**
     * Get/set Registry object
     *
     * The registry object is used to cache all application objects that are
     * shared across the whole app. This registry is itself automatically
     * cached to file during garbage collection.
     *
     * @param PHPFrame_FileRegistry $file_registry [Optional] A file registry
     *                                             object used to cache
     *                                             application wide data to
     *                                             file.
     *
     * @return PHPFrame_FileRegistry
     * @since  1.0
     */
    public function registry(PHPFrame_FileRegistry $file_registry=null)
    {
        if (!is_null($file_registry)) {
            $this->_registry = $file_registry;

        } elseif (is_null($this->_registry)) {
            $cache_file = $this->_tmp_dir.DS."app.reg";
            $this->_registry = new PHPFrame_FileRegistry($cache_file);
        }

        return $this->_registry;
    }

    /**
     * Get/set database object.
     *
     * @param PHPFrame_Database $db [Optional] Instance of PHPFrame_Database.
     *
     * @return PHPFrame_Database
     * @since  1.0
     */
    public function db(PHPFrame_Database $db=null)
    {
        if (!is_null($db)) {
            $this->_db = $db;

        } elseif (is_null($this->_db)) {
            $options = $this->config()->getSection("db");

            if (!array_key_exists("enable", $options) || !$options["enable"]) {
                $msg  = "Can not get database object because database is not ";
                $msg .= "enabled in configuration file.";
                throw new LogicException($msg);
            }

            if (!array_key_exists("driver", $options)
                || !array_key_exists("name", $options)
            ) {
                $msg  = "'driver' and 'name' are required in options array";
                throw new InvalidArgumentException($msg);
            }

            // Make absolute path for sqlite db if relative given
            if (strtolower($options["driver"]) == "sqlite"
                && !preg_match('/^\//', $options["name"])
            ) {
                $options["name"] = $this->_var_dir.DS.$options["name"];
            }

            $this->_db = PHPFrame_DatabaseFactory::getDB($options);
        }

        return $this->_db;
    }

    /**
     * Get/set Mailer object
     *
     * @param PHPFrame_Mailer $mailer Mailer object.
     *
     * @return PHPFrame_Mailer
     * @since  1.0
     */
    public function mailer(PHPFrame_Mailer $mailer=null)
    {
        if (!is_null($mailer)) {
            $this->registry()->set("mailer", $mailer);

        } elseif (is_null($this->registry()->get("mailer"))
            && $this->config()->get("smtp.enable")
        ) {
            $options = $this->config()->getSection("smtp");
            $mailer  = new PHPFrame_Mailer($options);
            $this->registry()->set("mailer", $mailer);
        }

        return $this->registry()->get("mailer");
    }

    /**
     * Get/set IMAP object used for incoming email.
     *
     * @param PHPFrame_IMAP $imap [Optional] IMAP object.
     *
     * @return PHPFrame_IMAP
     * @since  1.0
     */
    public function imap(PHPFrame_IMAP $imap=null)
    {
        $imap_enabled = (bool) $this->config()->get("imap.enable");

        if (!is_null($imap)) {
            $this->registry()->set("imap", $imap);

        } elseif (is_null($this->registry()->get("imap")) && $imap_enabled) {
            $this->registry()->set(
                "imap",
                new PHPFrame_IMAP(
                    $this->config()->get("imap.host"),
                    $this->config()->get("imap.user"),
                    $this->config()->get("imap.pass")
                )
            );
        }

        return $this->registry()->get("imap");
    }

    /**
     * Get/set Libraries object.
     *
     * @param PHPFrame_Libraries $libraries [Optional] Libraries object.
     *
     * @return PHPFrame_Libraries
     * @since  1.0
     */
    public function libraries(PHPFrame_Libraries $libraries=null)
    {
        if (!is_null($libraries)) {
            $this->registry()->set("libraries", $libraries);

        } elseif (is_null($this->registry()->get("libraries"))) {
            // Create mapper for PHPFrame_LibInfo object
            $mapper = new PHPFrame_Mapper(
                "PHPFrame_LibInfo",
                $this->_config_dir,
                "lib"
            );

            $this->registry()->set("libraries", new PHPFrame_Libraries($mapper));
        }

        return $this->_registry->get("libraries");
    }

    /**
     * Get/set Plugins object.
     *
     * @param PHPFrame_Plugins $plugins [Optional] Plugins object.
     *
     * @return PHPFrame_Plugins
     * @since  1.0
     */
    public function plugins(PHPFrame_Plugins $plugins=null)
    {
        if (!is_null($plugins)) {
            $this->registry()->set("plugins", $plugins);

        } elseif (is_null($this->registry()->get("plugins"))) {
            // Create mapper for PHPFrame_Plugins object
            $mapper = new PHPFrame_Mapper(
                "PHPFrame_PluginInfo",
                $this->_config_dir,
                "plugins"
            );

            $this->registry()->set("plugins", new PHPFrame_Plugins($mapper));
        }

        return $this->_registry->get("plugins");
    }

    /**
     * Get/set Logger object.
     *
     * @param PHPFrame_Logger $logger [Optional] Logger object to be used in
     *                                application.
     *
     * @return PHPFrame_Logger
     * @since  1.0
     */
    public function logger(PHPFrame_Logger $logger=null)
    {
        $log_level = $this->config()->get("debug.log_level");
        if ($log_level <= 0) {
            return;
        }

        if (!is_null($logger)) {
            $this->_setLogger($logger);

        } elseif (is_null($this->registry()->get("logger"))) {
            $logger = new PHPFrame_TextLogger(
                $this->_var_dir.DS."app.log",
                $log_level
            );

            $this->registry()->set("logger", $logger);
        }

        return $this->registry()->get("logger");
    }

    /**
     * Get/set Informer object.
     *
     * @param PHPFrame_Informer $informer [Optional] Informer object to be used
     *                                    in application.
     *
     * @return PHPFrame_Informer
     * @since  1.0
     */
    public function informer(PHPFrame_Informer $informer=null)
    {
        $informer_level = $this->config()->get("debug.informer_level");

        if ($informer_level <= 0) {
            return;
        }

        if (!is_null($informer)) {
            $this->_setInformer($informer);

        } elseif (is_null($this->registry()->get("informer"))) {
            // Create informer
            $recipients = $this->config()->get("debug.informer_recipients");
            $recipients = explode(",", $recipients);
            $mailer     = $this->mailer();

            if (!$mailer instanceof PHPFrame_Mailer) {
                $msg  = "Can not create informer object. No mailer has been ";
                $msg .= "loaded in application. Please check the 'smtp' ";
                $msg .= "section in the config file.";
                throw new LogicException($msg);
            }

            $this->registry()->set(
                "informer",
                new PHPFrame_Informer($mailer, $recipients, $informer_level)
            );
        }

        return $this->registry()->get("informer");
    }

    /**
     * Get/set Crypt object.
     *
     * @param PHPFrame_Crypt $crypt [Optional]
     *
     * @return PHPFrame_Crypt
     * @since  1.0
     */
    public function crypt(PHPFrame_Crypt $crypt=null)
    {
        if (!is_null($crypt)) {
            $this->registry()->set("crypt", $crypt);
        } elseif (is_null($this->registry()->get("crypt"))) {
            $secret = $this->config()->get("secret");
            $this->registry()->set("crypt", new PHPFrame_Crypt($secret));
        }

        return $this->registry()->get("crypt");
    }

    /**
     * Get/set reference to MVC factory object.
     *
     * @param PHPFrame_MVCFactory $mvc_factory [Optional] Reference to
     *                                         PHPFrame_MVCFactory object.
     *
     * @return PHPFrame_MVCFactory
     * @since  1.0
     */
    public function factory(PHPFrame_MVCFactory $mvc_factory=null)
    {
        if (!is_null($mvc_factory)) {
            $this->_mvc_factory = $mvc_factory;
        }

        return $this->_mvc_factory;
    }

    /**
     * Get/set the userland class prefix.
     *
     * @param string $str [Optional] The new class suffix.
     *
     * @return string
     * @since  1.0
     */
    public function classPrefix($str=null)
    {
        if (!is_null($str)) {
            $this->_class_prefix = trim((string) $str);
        }

        return $this->_class_prefix;
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
        // If no request is passed we try to use request object cached in app
        // or a new request is created using the session's client
        if (is_null($request)) {
            $request = $this->request();
        } else {
            $this->request($request);
        }

        // Register installed plugins with plugin handler
        foreach ($this->plugins() as $plugin) {
            if ($plugin->enabled()) {
                $plugin_name = $plugin->name();
                $this->_plugin_handler->registerPlugin(new $plugin_name($this));
            }
        }

        // Invoke route startup hook before request object is initialised
        $this->_plugin_handler->handle("routeStartup");

        // If no controller has been set we use de default value provided in
        // etc/phpframe.ini
        $controller_name = $request->controllerName();
        if (is_null($controller_name) || empty($controller_name)) {
            $def_controller = $this->config()->get("default_controller");
            $request->controllerName($def_controller);
        }

        // Invoke route shutdown hook
        $this->_plugin_handler->handle("routeShutdown");

        // Invoke dispatchLoopStartup hook
        $this->_plugin_handler->handle("dispatchLoopStartup");

        while (!$request->dispatched()) {
            // Set request as dispatched
            $request->dispatched(true);

            // Invoke preDispatch hook for every iteration of the dispatch loop
            $this->_plugin_handler->handle("preDispatch");

            // If any plugin set dispatched to false we start a new iteration
            if (!$request->dispatched()) {
                $request->dispatched(true);
                continue;
            }

            // Get requested controller name
            $controller_name = $request->controllerName();

            // Create the action controller
            $mvc_factory = $this->factory();
            $controller  = $mvc_factory->getActionController($controller_name);

            // Attach observers to the action controller
            $controller->attach($this->session()->getSysevents());

            $log_level = $this->config()->get("debug.log_level");
            if ($log_level > 0) {
                $controller->attach($this->logger());
            }

            $informer_level = $this->config()->get("debug.informer_level");
            if ($informer_level > 0) {
                $controller->attach($this->informer());
            }

            // Execute the action in the given controller
            $controller->execute();

            // Invoke postDispatch hook for every iteration of the dispatch loop
            $this->_plugin_handler->handle("postDispatch");

            // Redirect if set in controller
            $status_code  = $this->response()->statusCode();
            $redirect_url = $this->response()->header("Location");

            if (in_array($status_code, array(301, 303, 307))) {
                if (!$redirect_url) {
                    $msg  = "HTTP status code was set to ".$status_code." but ";
                    $msg .= "no redirect URL was specified in the Location ";
                    $msg .= "header.";
                    throw new LogicException($msg);
                }

                $this->session()->getClient()->redirect($redirect_url);
                return;
            }
        }

        // Invoke dispatchLoopShutdown hook
        $this->_plugin_handler->handle("dispatchLoopShutdown");

        $this->output();
    }

    /**
     * Process response and send output.
     *
     * @return void
     * @since  1.0
     */
    protected function output()
    {
        $request  = $this->request();
        $response = $this->response();

        // Invoke dispatchLoopShutdown hook
        $this->_plugin_handler->handle("preApplyTheme");

        // Apply theme if needed
        $document  = $response->document();
        $renderer  = $response->renderer();
        $sysevents = $this->session()->getSysevents();

        if ($document instanceof PHPFrame_HTMLDocument) {
            if (!$request->ajax()) {
                $theme       = $this->config()->get("theme");
                $base_url    = $this->config()->get("base_url");
                $theme_url   = $base_url."themes/".$theme;
                $theme_path  = $this->getInstallDir().DS."public".DS."themes";
                $theme_path .= DS.$theme.DS."index.php";
                $document->applyTheme($theme_url, $theme_path, $this);
            } else {
                // Append system events when no theme
                $document->prependBody($renderer->render($sysevents));
                $sysevents->clear();

                // Set "body only" mode for AJAX requests when HTML document
                $document->bodyOnly(true);
            }

        } elseif ($renderer instanceof PHPFrame_RPCRenderer) {
            if (count($sysevents) > 0) {
                $sysevents->statusCode($response->statusCode());
                $renderer->render($sysevents);
            }
        }

        // Invoke postApplyTheme hook
        $this->_plugin_handler->handle("postApplyTheme");

        // If not in quiet mode, send response back to the client
        if (!$request->quiet()) {
            $response->send();
        }

        // If outfile is defined we write the response to file
        $outfile = $request->outfile();
        if (!empty($outfile)) {
            $file_obj = new SplFileObject($outfile, "w");
            $file_obj->fwrite((string) $response);
        }
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
    private function _setConfig(PHPFrame_Config $config)
    {
        // Check that config object has required data
        $array    = iterator_to_array($config);
        $req_keys = array("app_name", "base_url");
        foreach ($req_keys as $req_key) {
            if (!isset($array[$req_key]) || empty($array[$req_key])) {
                $msg  = "Could not set configuration object. Config must ";
                $msg .= "contain a value for '".$req_key."'. ";
                $msg .= "To set this configuration parameter you can edit ";
                $msg .= "the configuration file stored in ";
                $msg .= "'".$config->getPath()."'.";
                throw new RuntimeException($msg);
            }
        }

        $this->_config = $config;

        // Set timezone
        date_default_timezone_set($config->get("timezone"));

        // Set display_exceptions in exception handler
        $display_exceptions = $config->get("debug.display_exceptions");
        PHPFrame_ExceptionHandler::displayExceptions($display_exceptions);
    }
}
