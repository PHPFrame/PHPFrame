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
        
        // Invoke route startup hook before request object is initialised
        $this->_plugin_handler->handle("routeStartup");
        
        // Initialise request
        $request = PHPFrame::Request();
        
        // Get instance of client from session
        $client = PHPFrame::Session()->getClient();
        // Prepare response using client
        $client->prepareResponse(PHPFrame::Response());
        
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
        /**
         * Register MVC autoload function
         */
        spl_autoload_register(array("PHPFrame_MVCFactory", "__autoload"));
        
        // Invoke dispatchLoopStartup hook
        $this->_plugin_handler->handle("dispatchLoopStartup");
        
        $request = PHPFrame::Request();
        
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
            $controller = PHPFrame_MVCFactory::getActionController($controller_name);
            
            // Attach observers to the action controller
            $controller->attach(PHPFrame::Session()->getSysevents());
            
            // Execute the action in the given controller
            $controller->execute();
            
            // Invoke postDispatch hook for every iteration of the dispatch loop
            $this->_plugin_handler->handle("postDispatch");
        }
        
        // Invoke dispatchLoopShutdown hook
        $this->_plugin_handler->handle("dispatchLoopShutdown");
        
        $response = PHPFrame::Response();
        
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
        $profiler_enable  = PHPFrame::Config()->get("debug.profiler_enable");
        $profiler_display = PHPFrame::Config()->get("debug.profiler_display");
        $profiler_outdir  = PHPFrame::Config()->get("debug.profiler_outdir");
        
        if ($profiler_enable) {
            $profiler = PHPFrame_Profiler::instance();
            
            // Add final milestone
            $profiler->addMilestone();
            
            // Get profiler output by casting object to string
            $profiler_out = (string) $profiler;
            
            // Display output if set in config
            if ($profiler_display) {
                if (PHPFrame::Session()->getClientName() != "cli") {
                    echo "<pre>";
                }
                
                // Display output
                echo "Profiler Output:\n\n";
                echo $profiler_out;
            }
            
            // Dump profiler output to file is outdir is specified in config
            if (!empty($profiler_outdir)) {
                $profiler_outfile = $profiler_outdir.DS.time().".ppo";
                $file_obj = new PHPFrame_FileObject($profiler_outfile, "w");
                $file_obj->fwrite($profiler_out);
            }
        }
        
        // Exit setting status to 0, 
        // which indicates that program terminated successfully
        exit(0);
    }
}
