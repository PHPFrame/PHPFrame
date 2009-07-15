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
     * Constructor
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct()
    {
        // Set profiler milestone
        PHPFrame_Debug_Profiler::setMilestone('Start');
        
        // Load language files
        $this->_loadLanguage();
        
        // Set timezone
        date_default_timezone_set(config::TIMEZONE);
        
        // Get/init session object
        $session = PHPFrame::Session();
        
        // Check dependencies
        PHPFrame_Application_Dependencies::check($session);
        
        // Rewrite Request URI
        PHPFrame_Utils_Rewrite::rewriteRequest();
        
        // Initialise request
        $request = PHPFrame::Request();
        
        // Set profiler milestone
        PHPFrame_Debug_Profiler::setMilestone('Front controller constructed');
    }
    
    /**
     * Run
     * 
     * @access public
     * @return void
     * @uses   PHPFrame, PHPFrame_MVC_ActionController, PHPFrame_Environment_IClient
     *         PHPFrame_Application_Response
     * @since  1.0
     */
    public function run() 
    {
        $component_name = PHPFrame::Request()->getComponentName();
        
        // set the component path
        define("COMPONENT_PATH", _ABS_PATH.DS."src".DS."components".DS.$component_name);
        
        // Get instance of client from session
        $client = PHPFrame::Session()->getClient();
        // Prepare response using client
        $client->prepareResponse(PHPFrame::Response());
        
        // Create the action controller
        $controller = PHPFrame_MVC_ActionController::getInstance($component_name);
        // Check that action controller is of valid type and run it if it is
        if ($controller instanceof PHPFrame_MVC_ActionController) {
            // Execute task
            $controller->execute();
        }
        else {
            throw new PHPFrame_Exception("Controller not supported.");
        }
    }
    
    /**
     * Load language files
     * 
     * @access private
     * @return void
     * @since  1.0
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
