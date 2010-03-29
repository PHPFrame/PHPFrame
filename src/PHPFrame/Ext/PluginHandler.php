<?php
/**
 * PHPFrame/Ext/PluginHandler.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Ext
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * This class handles the plugin hooks and allows plugins to register to
 * listen for the hook events.
 *
 * This class is instantiated and used by the Application class.
 *
 * @category PHPFrame
 * @package  Ext
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_PluginHandler
{
    /**
     * Absolute path to plugins directory.
     *
     * @var string
     */
    private static $_plugins_path;
    /**
     * This property holds an instance of SplObjectStorage used to store
     * instances of the registered plugins.
     *
     * @var SplObjectStorage
     */
    private $_plugins = null;
    /**
     * An array defining the support events or hooks
     *
     * @var array
     */
    private $_events = array(
        "routeStartup",
        "routeShutdown",
        "dispatchLoopStartup",
        "dispatchLoopShutdown",
        "preDispatch",
        "postDispatch",
        "preApplyTheme",
        "postApplyTheme"
    );
    /**
     * Reference to the application object.
     *
     * @var PHPFrame_Request
     */
    private $_app;

    /**
     * Constructor
     *
     * @param PHPFrame_Application $app Reference to application object.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Application $app)
    {
        // Store plugins path in static property to make it available in
        // static autoload method.
        self::$_plugins_path = $app->getInstallDir().DS."src".DS."plugins";

        // Store reference to application object in private property
        $this->_app = $app;

        // Acquire instance of SplObjectStorage
        $this->_plugins = new SplObjectStorage();

        /**
         * Register plugins autoload function
         */
        spl_autoload_register(array($this, "autoload"));
    }

    /**
     * Plugins autoloader method
     *
     * @param string $class_name The class name to load
     *
     * @static
     * @return void
     * @since  1.0
     */
    public static function autoload($class_name)
    {
        $file_name  = self::$_plugins_path.DS;
        $file_name .= trim($class_name).".php";

        if (is_file($file_name)) {
            include $file_name;
            return;
        }

        // If file not found try lowercase file name for backward compatibility
        $file_name  = self::$_plugins_path.DS;
        $file_name .= strtolower(trim($class_name)).".php";

        if (is_file($file_name)) {
            include $file_name;
            return;
        }
    }

    /**
     * Register a plugin with the plugin handler
     *
     * @param PHPFrame_Plugin $plugin Instance of the plugin object we want to
     *                                register.
     *
     * @return void
     * @since  1.0
     */
    public function registerPlugin(PHPFrame_Plugin $plugin)
    {
        $this->_plugins->attach($plugin);
    }

    /**
     * Unregister a plugin from the plugin handler
     *
     * @param PHPFrame_Plugin $plugin Instance of the plugin object we want to
     *                                unregister.
     *
     * @return void
     * @since  1.0
     */
    public function unregisterPlugin(PHPFrame_Plugin $plugin)
    {
        $this->_plugins->detach($plugin);
    }

    /**
     * Handle plugin events (hooks)
     *
     * @param string $event The plugin handler supports the following events or
     *                      hooks: routeStartup, routeShutdown,
     *                      dispatchLoopStartup, dispatchLoopShutdown,
     *                      preDispatch, postDispatch, preApplyTheme and
     *                      postApplyTheme.
     *
     * @return void
     * @since  1.0
     */
    public function handle($event)
    {
        $event = trim((string) $event);
        if (!in_array($event, $this->_events)) {
            $msg  = "Plugin event not supported. The plugin handler supports ";
            $msg .= "'".implode("', '", $this->_events)."'";
            throw new LogicException($msg);
        }

        foreach ($this->_plugins as $plugin) {
            $plugin->$event();
        }
    }
}
