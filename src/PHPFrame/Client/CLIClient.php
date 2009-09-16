<?php
/**
 * PHPFrame/Client/CLIClient.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Client
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @ignore
 */

/**
 * Client for Command Line Interface
 * 
 * @category PHPFrame
 * @package  Client
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 * @ignore
 */
class PHPFrame_CLIClient implements PHPFrame_IClient
{
    /**
     * Check if this is the correct helper for the client being used
     * 
     * @static
     * @access public
     * @return PHPFrame_IClient|boolean Object instance of this class 
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
    public function populateRequest(PHPFrame_RequestRegistry $request) 
    {
        // create the parser
        $parser = new Console_CommandLine(array(
            'description' => PHPFrame::Config()->get("app_name"),
            'version'     => PHPFrame::Config()->get("version")
        ));
        
        // add an option to delete original files after zipping
        $parser->addOption(
            'quiet',
            array(
                'short_name'  => '-q',
                'long_name'   => '--quiet',
                'action'      => 'StoreTrue',
                'description' => 'Operate in quiet mode (no output)'
            )
        );
        
        // add the files argument, the user can specify one or several files
        $parser->addOption(
            'infile',
            array(
                'short_name'  => '-i',
                'long_name'   => '--infile',
                'description' => 'List of input files separated by commas',
                'optional'    => true
            )
        );
        
        // add the out file name argument
        $parser->addOption(
            'outfile', 
            array(
                'short_name'  => '-o',
                'long_name'   => '--outfile',
                'description' => 'File to save the output',
                'optional'    => true
            )
        );
        
        $parser->addArgument(
            'controller', 
            array(
                'description' => 'The controller to run',
                'optional'    => true
            )
        );
        
        $parser->addArgument(
            'action', 
            array(
                'description' => 'The action to run',
                'optional'    => true
            )
        );
        
        $parser->addArgument(
            'params', 
            array(
                'multiple' => true,
                'description' => 'List of request parameters separated by spaces',
                'optional'    => true
            )
        );
        
        try {
            // Create request array to be used for return
            $request = array();
            
            // Parse input options
            $result = $parser->parse();
            
            // Get params array if isset
            if (isset($result->args['params'])) {
                $params = $result->args['params'];
                unset($result->args['params']);
                foreach ($params as $param) {
                    parse_str($param, $param_pair);
                    // Store param in request array
                    $request = array_merge($request, $param_pair);
                }
            }
            
            $request = array_merge($result->options, $result->args, $request);
            
            return array("request" => $request);
            
        } catch (Exception $e) {
            $parser->displayError($e->getMessage());
            exit;
        }
    }
    
    /**
     * Prepare response
     * 
     * This method is invoked by the front controller before invoking the requested
     * action in the action controller. It gives the client an opportunity to do 
     * something before the component is executed.
     * 
     * @param PHPFrame_Response $response The response object to prepare.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function prepareResponse(PHPFrame_Response $response) 
    {
        // Automatically log in as system user
        $user = new PHPFrame_User();
        $user->setId(1);
        $user->setGroupId(1);
        $user->setUserName('system');
        $user->setFirstName('System');
        $user->setLastName('User');
        
        // Store user in session
        $session = PHPFrame::Session();
        $session->setUser($user);
        
        // Automatically set session token in request so that forms will be allowed
        PHPFrame::Request()->setParam($session->getToken(), 1);
        
        // Set document as response content
        $response->setDocument(new PHPFrame_PlainDocument());
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
                PHPFrame::Request()->setParam($key, $value);
            }
        }
        
        // Retrigger the app
        PHPFrame::Fire();
    }
}
