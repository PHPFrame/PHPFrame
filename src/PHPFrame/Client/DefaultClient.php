<?php
/**
 * PHPFrame/Client/DefaultClient.php
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
 * Client used by default (PC HTTP browsers or anything for which no helper 
 * exists)
 * 
 * @category PHPFrame
 * @package  Client
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since    1.0
 * @ignore
 */
class PHPFrame_DefaultClient extends PHPFrame_Client
{
    /**
     * Check if this is the correct helper for the client being used
     * 
     * @static
     * @return PHPFrame_Client|boolean Instance of this class if correct
     *                                 helper for client or false otherwise.
     * @since  1.0
     */
    public static function detect() 
    {
        //this is our last hope to find a helper, just return instance
        return new self;
    }
    
    /**    
     * Get client name.
     * 
     * @return string Name to identify client type.
     * @since  1.0
     */
    public function getName() 
    {
        return "default";
    }
    
    /**    
     * Populate the Request object.
     * 
     * @return void
     * @since  1.0
     */
    public function populateRequest(PHPFrame_Request $request) 
    {
        // Get an instance of PHP Input filter
        $inputfilter = new InputFilter();
            
        // Process incoming request arrays and store filtered data in class
        $filtered_request = $inputfilter->process($_REQUEST);
        
        // Populate request params
        foreach ($filtered_request as $key=>$value) {
            if ($key == "controller") {
                $request->setControllerName($value);
            } elseif ($key == "action") {
                $request->setAction($value);
            } elseif ($key == "ajax") {
                $request->setAJAX($value);
            } else {
                $request->setParam($key, $value);
            }
        }
        
        foreach ($_SERVER as $key=>$value) {
            if (substr($key, 0, 5) == "HTTP_") {
                $key = str_replace('_', ' ', substr($key, 5));
                $key = str_replace(' ', '-', ucwords(strtolower($key)));
                $request->setHeader($key, $value);
            } elseif ($key == "REQUEST_METHOD") {
                $request->setMethod($value);
            } elseif ($key == "REMOTE_ADDR") {
                $request->setRemoteAddr($value);
            } elseif ($key == "REQUEST_URI") {
                $request->setRequestURI($value);
            } elseif ($key == "SCRIPT_NAME") {
                $request->setScriptName($value);
            } elseif ($key == "QUERY_STRING") {
                $request->setQueryString($value);
            } elseif ($key == "REQUEST_TIME") {
                $request->setRequestTime($value);
            }
        }
        
        foreach ($_FILES as $key=>$value) {
            if (is_array($value) && !empty($value["name"])) {
                $request->attachFile($key, $value);
            }
        }
        
        return;
    }
    
    /**
     * Prepare response
     * 
     * This method is invoked by the front controller before invoking the 
     * requested action in the action controller. It gives the client an 
     * opportunity to do something before the component is executed.
     * 
     * @param PHPFrame_Response $response   The response object to prepare.
     * @param string            $views_path Absolute path to vies dir.
     * 
     * @return void
     * @since  1.0
     */
    public function prepareResponse(PHPFrame_Response $response, $views_path) 
    {
        // Set document as response content
        $response->setDocument(new PHPFrame_HTMLDocument());
        
        // Set response renderer
        $response->setRenderer(new PHPFrame_HTMLRenderer($views_path));
    }
    
    /**
     * Handle controller redirection
     * 
     * @param string $url
     * 
     * @return void
     * @since  1.0
     */
    public function redirect($url)
    {
        $url = trim((string) $url);
        
        if (!$url) {
            return;
        }
        
        if (!headers_sent()) {
            header("Location: ".$url);
        } else {
        	echo '<meta http-equiv="refresh" content="1; URL='.$url.'">';
        }
        
        exit(0);
    }
}
