<?php
/**
 * PHPFrame/Client/CLIClient.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Client
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @ignore
 */

/**
 * Client for Command Line Interface
 * 
 * @category PHPFrame
 * @package  Client
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since    1.0
 * @ignore
 */
class PHPFrame_CLIClient extends PHPFrame_Client
{
    /**
     * Check if this is the correct helper for the client being used
     * 
     * @static
     * @access public
     * @return PHPFrame_Client|boolean Instance of this class if correct helper
     *                                  for client or FALSE otherwise.
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
    public function populateRequest(PHPFrame_Request $request) 
    {
        // Automatically log in as system user
        $user = new PHPFrame_User();
        $user->setId(1);
        $user->setGroupId(1);
        $user->setUserName('system');
        $user->setFirstName('System');
        $user->setLastName('User');
        
        // Store user in session
        $session = PHPFrame::getSession();
        $session->setUser($user);
        
        // Automatically set session token in request to allow forms
        $request->setParam($session->getToken(), 1);
        
        // create the CLI parser
        $parser = new Console_CommandLine();
        
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
                'multiple'=>true,
                'description'=>'List of request parameters separated by spaces',
                'optional'=>true
            )
        );
        
        try {
            // Parse input options
            $result = $parser->parse();
            
            $request->setControllerName($result->args["controller"]);
            $request->setAction($result->args["action"]);
            
            global $argv;
            $request->setScriptName($argv[0]);
            
            $request->setRequestTime(time());
            $request->setQuiet($result->options["quiet"]);
            $request->setMethod("CLI");
            
            $request->setOutfile($result->options["outfile"]);
            
            if ($result->options["infile"]) {
                $infile = new PHPFrame_FileObject($result->options["infile"]);
        
                $request->attachFile("infile", array(
                    "tmp_name"=>$infile->getPath(),
                    "name"=>$infile->getFilename(),
                    "size"=>$infile->getSize(),
                    "type"=>$infile->getType(),
                    "error"=>null
                ));
            }
            
            foreach ($result->args["params"] as $param) {
                parse_str($param, $param_pair);
                foreach ($param_pair as $param_key=>$param_value) {
                    $request->setParam($param_key, $param_value);
                }
            }
            
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
        // Set document as response content
        $response->setDocument(new PHPFrame_PlainDocument());
        
        // Set response renderer
        $response->setRenderer(new PHPFrame_PlainRenderer());
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
