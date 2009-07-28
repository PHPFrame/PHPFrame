<?php
/**
 * PHPFrame/Client/CLI.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Client
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Client for Command Line Interface
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Client
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Client_CLI implements PHPFrame_Client_IClient
{
    /**
     * Check if this is the correct helper for the client being used
     * 
     * @static
     * @access public
     * @return PHPFrame_Client_IClient|boolean Object instance of this class 
     *                                         if correct helper for client 
     *                                         or false otherwise.
     * @since  1.0
     */
    public static function detect() 
    {
        global $argv;
        
        if (is_array($argv)) {
            return new self;
        }
        
        return false;
    }
    
    /**    
     * Get client name
     * 
     * @access public
     * @return string Name to identify helper type
     * @since  1.0
     */
    public function getName() 
    {
        return "cli";
    }
    
    /**    
     * Populate the Unified Request array
     * 
     * @access public
     * @return array  Unified Request Array
     * @since  1.0
     */
    public function populateRequest() 
    {
        // Get arguments passed via command line and parse them as request vars
        global $argv;
        
        $request = array();
        $keys = array();
        
        for ($i=1; $i<count($argv); $i++) {
            // If two hyphens passed we take as full flag
            if (preg_match('/^\-\-[a-zA-Z]+/', $argv[$i], $matches)) {
                $keys[] = substr($argv[$i], 2);
            // If we get only one hyphen we try to replace short names
            } elseif (preg_match('/^\-[a-zA-Z]+/', $argv[$i], $matches)) {
                $key = substr($argv[$i], 1);
                
                if ($key == "c") {
                    $key = "controller";
                } elseif ($key == "a") {
                    $key = "action";
                }
                
                $keys[] = $key;
            // Else we add the value to the last key
            } else {
                $request['request'][end($keys)] = $argv[$i];
            }
        }
        
        return $request;
    }
    
    /**
     * Prepare response
     * 
     * This method is invoked by the front controller before invoking the requested
     * action in the action controller. It gives the client an opportunity to do 
     * something before the component is executed.
     * 
     * @param PHPFrame_Application_Response $response The response object to prepare.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function prepareResponse(PHPFrame_Application_Response $response) 
    {
        // Automatically log in as system user
        $user = new PHPFrame_User();
        $user->set('id', 1);
        $user->set('groupid', 1);
        $user->set('username', 'system');
        $user->set('firstname', 'System');
        $user->set('lastname', 'User');
        
        // Store user in session
        $session = PHPFrame::Session();
        $session->setUser($user);
        
        // Automatically set session token in request so that forms will be allowed
        PHPFrame::Request()->set($session->getToken(), 1);
        
        // Set document as response content
        $response->setDocument(new PHPFrame_Document_Plain());
    }
    
    public function redirect($url)
    {
        // Reset the request
        PHPFrame::Request()->destroy();
        
        // Get query params from redirection url
        $url = parse_url($url);
        
        if (isset($url["query"])) {
            parse_str($url["query"], $params);
            
            // Loop through URL params and set values in request
            foreach ($params as $key=>$value) {
                $_REQUEST[$key] = $value;
                $_GET[$key] = $value;
                PHPFrame::Request()->set($key, $value);
            }
        }
        
        // Retrigger the app
        PHPFrame::Fire();
    }
}
