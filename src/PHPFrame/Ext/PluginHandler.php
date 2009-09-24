<?php
/**
 * PHPFrame/Ext/PluginHandler.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Ext
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @ignore
 */

/**
 * This class handles the plugin hooks and allows plugins to register to 
 * listen for the hook events.
 * 
 * This class is instantiated and used by the FrontController.
 * 
 * @category PHPFrame
 * @package  Ext
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 * @see      PHPFrame_FrontController
 */
class PHPFrame_PluginHandler
{
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
        "postDispatch"
    );
    
    /**
     * Constructor
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct()
    {
        // Acquire instance of SplObjectStorage
        $this->_plugins = new SplObjectStorage();
        
        /**
         * Register plugins autoload function
         */
        spl_autoload_register(array("PHPFrame_PluginHandler", "__autoload"));
    }
    
    /**
     * Plugins autoloader method
     * 
     * @param string $class_name The class name to load
     * 
     * @static
     * @access public
     * @return void
     * @since  1.0
     */
    public static function __autoload($class_name)
    {
        $plugins_path  = PHPFRAME_INSTALL_DIR.DS."src".DS."plugins";
        $file_name     = $plugins_path.DS.strtolower(trim($class_name)).".php";
        
        if (is_file($file_name)) {
            require $file_name;
        }
    }
    
    /**
     * Register a plugin with the plugin handler
     * 
     * @param PHPFrame_Plugin $plugin Instance of the plugin object we want to 
     *                                register.
     * 
     * @access public
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
     * @access public
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
     *                      preDispatch, postDispatch
     * 
     * @access public
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