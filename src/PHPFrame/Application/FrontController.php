<?php
/**
 * PHPFrame/Application/FrontController.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Application
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * The FrontController's main responsibility is to run the MVC app and provide 
 * hooks for plugins.
 * 
 * @category PHPFrame
 * @package  Application
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see      PHPFrame
 * @since    1.0
 * @internal
 */
class PHPFrame_FrontController
{
    /**
     * An instance of PluginHandler that the front controller will use to 
     * provide hooks for plugins.
     * 
     * @var PHPFrame_PluginHandler
     */
    private $_plugin_handler;
    
    /**
     * Constructor
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct()
    {
        // Set profiler milestone
        PHPFrame_Profiler::instance()->addMilestone();
        
        // Acquire instance of Plugin Handler
        $this->_plugin_handler = new PHPFrame_PluginHandler();
        
        // Register installed plugins with plugin handler
        foreach (PHPFrame::AppRegistry()->getPlugins() as $plugin) {
            if ($plugin->isEnabled()) {
                $plugin_name = $plugin->getName();
                $this->_plugin_handler->registerPlugin(new $plugin_name());
            }
        }
        
        // Invoke route startup hook
        $this->_plugin_handler->handle("routeStartup");
        
        // Initialise request
        $request = PHPFrame::Request();
        
        // Invoke route shutdown hook
        $this->_plugin_handler->handle("routeShutdown");
    }
    
    /**
     * Run
     * 
     * @access public
     * @return void
     * @uses   PHPFrame_SessionRegistry, PHPFrame_IClient, PHPFrame_Response, 
     *         PHPFrame_RequestRegistry, PHPFrame_MVCFactory, 
     *         PHPFrame_ActionController
     * @since  1.0
     * @todo   Dispatch loop is not reallu looping at the moment as 
     *         $controller->execute(); directly delegates to redirect or send 
     *         a response. This needs some refactoring to accomodate the plugin
     *         hooks.
     */
    public function run() 
    {
        // Get instance of client from session
        $client = PHPFrame::Session()->getClient();
        // Prepare response using client
        $client->prepareResponse(PHPFrame::Response());
        
        /**
         * Register MVC autoload function
         */
        spl_autoload_register(array("PHPFrame_MVCFactory", "__autoload"));
        
        $this->_plugin_handler->handle("dispatchLoopStartup");
        
        $dispatched = false;
        
        while (!$dispatched) {
            $this->_plugin_handler->handle("preDispatch");
            
            // Get requested controller name
            $controller_name = PHPFrame::Request()->getControllerName();
    
            // Create the action controller
            $controller = PHPFrame_MVCFactory::getActionController($controller_name);
            
            // Attach observers to the action controller
            $controller->attach(PHPFrame::Session()->getSysevents());
            
            // Execute the action in the given controller
            $controller->execute();
            
            $this->_plugin_handler->handle("postDispatch");
        }
        
        $this->_plugin_handler->handle("dispatchLoopShutdown");
    }
}
