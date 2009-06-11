<?php
/**
 * PHPFrame/Application/FrontController.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Application
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * FrontController Class
 * 
 * This is the FrontController. Its main objective is to initialise the framework 
 * and decide which action controller should be run.
 * 
 * This class is still work in progress.
 * 
 * The class should be instantiated as:
 * 
 * <code>
 * $frontcontroller = PHPFrame::getFrontController();
 * </code>
 * 
 * Before we instantiate the FrontController we first need to set a few useful constants,
 * include the autoloader and the config file and then finally 
 * instantiate the FrontController and run it.
 * 
 * <code>
 * define("_EXEC", true);
 * define('_ABS_PATH', dirname(__FILE__) );
 * define( 'DS', DIRECTORY_SEPARATOR );
 * 
 * // include config
 * require_once _ABS_PATH.DS."inc".DS."config.php";
 * 
 * // Include autoloader
 * require_once _ABS_PATH.DS."inc".DS."autoload.php";
 * 
 * $frontcontroller = PHPFrame::getFrontController();
 * $frontcontroller->run();
 * </code>
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Application
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see        PHPFrame
 * @since      1.0
 */
class PHPFrame_Application_FrontController
{
    /**
     * Instance of itself in order to implement the singleton pattern
     * 
     * @var object of type PHPFrame_Application_FrontController
     */
    private static $_instance=null;
    
    /**
     * Constructor
     * 
     * @access    protected
     * @return     void
     * @since    1.0
     */
    private function __construct() 
    {
        // Set profiler milestone
        PHPFrame_Debug_Profiler::setMilestone('Start');
        
        // Initialise phpFame's error and exception handlers.
        PHPFrame_Exception_Handler::init();
        
        // Load language files
        $this->_loadLanguage();
        
        // Set timezone
        date_default_timezone_set(config::TIMEZONE);
        
        // Get/init session object
        $session = PHPFrame::getSession();
        
        // Check dependencies
        PHPFrame_Application_Dependencies::check($session);
        
        // Rewrite Request URI
        PHPFrame_Utils_Rewrite::rewriteRequest();
        
        // Initialise request
        $request = PHPFrame::getRequest();
        
        // Give the client a chance to do something before we move on to run
        $client = $session->getClient();
        $client->preActionHook();
        
        // Set profiler milestone
        PHPFrame_Debug_Profiler::setMilestone('Front controller constructed');
    }
    
    /**
     * Get Instance
     * 
     * @return PHPFrame_Application_FrontController
     */
    public static function getInstance() 
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self;
        }
        
        return self::$_instance;
    }
    
    /**
     * Run
     * 
     * This method executes the request and stores the component's output buffer in $this->component_output.
     * 
     * @access    public
     * @return    void
     * @since    1.0
     */
    public function run() 
    {
        $component_name = PHPFrame::getRequest()->getComponentName();
        
        // set the component path
        define("COMPONENT_PATH", _ABS_PATH.DS."src".DS."components".DS.$component_name);
        
        // Create the action controller
        $controller = PHPFrame::getActionController($component_name);
        // Check that action controller is of valid type and run it if it is
        if ($controller instanceof PHPFrame_Application_ActionController) {
            // Execute task
            $output = $controller->execute();
        }
        else {
            throw new PHPFrame_Exception("Controller not supported.");
        }
        
        // Set profiler milestone
        PHPFrame_Debug_Profiler::setMilestone('Action controller executed');
        
        // Render output using client's template
        $client = PHPFrame::getSession()->getClient();
        $client->renderTemplate($output);
        
        // Set profiler milestone
        PHPFrame_Debug_Profiler::setMilestone('Overall template rendered');
        
        // Build response and send it
        $response = PHPFrame::getResponse();
        $response->setBody($output);
        
        // Set profiler milestone
        PHPFrame_Debug_Profiler::setMilestone('Set response');
        
        $response->send();
    }
    
    /**
     * Load language files
     * 
     * @access    private
     * @return    void
     * @since    1.0
     */
    private function _loadLanguage() 
    {
        // load the application language file
        $lang_file = _ABS_PATH.DS."src".DS."lang".DS.config::DEFAULT_LANG.".php";
        if (file_exists($lang_file)) {
            require_once $lang_file;
        }
        else {
            throw new PHPFrame_Exception('Could not find language file ('.$lang_file.')');
        }
        
        // Include the PHPFrame lib language file
        $lang_file = "PHPFrame".DS."Lang".DS.config::DEFAULT_LANG.".php";
        if (!(require_once $lang_file)) {
            throw new PHPFrame_Exception('Could not find language file ('.$lang_file.')');
        }
    }
}
