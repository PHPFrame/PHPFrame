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
 * The FrontController's main objective is to initialise the framework 
 * and execute the requested controller action. 
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
     * Constructor
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct()
    {
        // Set profiler milestone
        PHPFrame_Profiler::setMilestone('Start');
        
        // Rewrite Request URI
        PHPFrame_Rewrite::rewriteRequest();
        
        // Initialise request
        $request = PHPFrame::Request();
        
        // Set profiler milestone
        PHPFrame_Profiler::setMilestone('Front controller constructed');
    }
    
    /**
     * Run
     * 
     * @access public
     * @return void
     * @uses   PHPFrame, PHPFrame_ActionController, PHPFrame_Environment_IClient
     *         PHPFrame_Response
     * @since  1.0
     */
    public function run() 
    {
        // Get instance of client from session
        $client = PHPFrame::Session()->getClient();
        // Prepare response using client
        $client->prepareResponse(PHPFrame::Response());
        
        // Get requested controller name
        $controller_name = PHPFrame::Request()->getControllerName();
        
        /**
         * Register MVC autoload function
         */
        spl_autoload_register(array("PHPFrame_MVCFactory", "__autoload"));

        // Create the action controller
        $controller = PHPFrame_MVCFactory::getActionController($controller_name);
        
        // Execute the action in the given controller
        $controller->execute();
    }
}
