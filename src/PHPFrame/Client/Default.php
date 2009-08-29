<?php
/**
 * PHPFrame/Client/Default.php
 * 
 * PHP version 5
 * 
 * @category PHPFrame
 * @package    PHPFrame
 * @subpackage Client
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Client used by default (PC HTTP browsers or anything for which no helper exists)
 * 
 * @category PHPFrame
 * @package    PHPFrame
 * @subpackage Client
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_Client_Default implements PHPFrame_Client_IClient
{
    /**
     * Check if this is the correct helper for the client being used
     * 
     * @static
     * @access public
     * @return PHPFrame_Client_IClient|boolean Object instance of this class if correct
     *                                         helper for client or false otherwise.
     */
    public static function detect() 
    {
        //TODO test checking for $_SERVER['HTTP_USER_AGENT']
        
        //this is our last hope to find a helper, just return instance
        return new self;
    }
    
    /**    
     * Get client name
     * 
     * @access public
     * @return string Name to identify helper type
     */
    public function getName() 
    {
        return "default";
    }
    
    /**    
     * Populate the Unified Request array
     * 
     * @access public
     * @return array  Unified Request Array
     */
    public function populateRequest() 
    {
        $request = array();
        
        // Get an instance of PHP Input filter
        $inputfilter = new InputFilter();
            
        // Process incoming request arrays and store filtered data in class
        $request['request'] = $inputfilter->process($_REQUEST);
        $request['get'] = $inputfilter->process($_GET);
        $request['post'] = $inputfilter->process($_POST);
            
        // Once the superglobal request arrays are processed we unset them
        // to prevent them being used from here on
        unset($_REQUEST, $_GET, $_POST);
        
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
        // add the jQuery + jQuery UI libraries to the HTML document
        // that we will use in the response. jQuery lib need to be loaded before 
        // we load the jQuery plugins in the component output.
        
        $document = new PHPFrame_Document_HTML();
        $document->addScript('lib/jquery/js/jquery-1.3.2.min.js');
        $document->addScript('lib/jquery/js/jquery-ui-1.7.custom.min.js');
        $document->addScript('lib/jquery/plugins/validate/jquery.validate.pack.js');
        $document->addScript('lib/jquery/plugins/form/jquery.form.pack.js');
        $document->addStyleSheet('lib/jquery/css/extranetoffice/jquery-ui-1.7.custom.css');
        
        // Set document as response content
        $response->setDocument($document);
    }
    
    public function redirect($url)
    {
        $url = (string) trim($url);
        
        if ($url) {
            $url = PHPFrame_Utils_Rewrite::rewriteURL($url);
            header("Location: ".$url);
            exit;
        }
    }
}
