<?php
/**
 * PHPFrame/Client/MobileClient.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Client
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 * @ignore
 */

/**
 * Client for Mobile Devices
 * 
 * @category PHPFrame
 * @package  Client
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 * @ignore
 */
class PHPFrame_MobileClient extends PHPFrame_Client
{
    /**    
     * Get client name
     * 
     * @return string Name to identify client type.
     * @since  1.0
     */
    public function getName() 
    {
        return "mobile";
    }
    
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
        if (isset($_SERVER["HTTP_X_WAP_PROFILE"])) {
            return new self;
        }
        
        if (isset($_SERVER["HTTP_ACCEPT"]) 
            && preg_match("/wap\.|\.wap/i", $_SERVER["HTTP_ACCEPT"])
        ) { 
            return new self;
        }
        
        if (isset($_SERVER["HTTP_USER_AGENT"])) {
            $user_agent = $_SERVER["HTTP_USER_AGENT"];
            if (preg_match("/Creative\ AutoUpdate/i", $user_agent)) {
                return new self;
            }
        
            $uamatches = array(
                "midp", "j2me", "avantg", "docomo", "novarra", "palmos", 
                "palmsource", "240x320", "opwv", "chtml", "pda", "windows\ ce", 
                "mmp\/", "blackberry", "mib\/", "symbian", "wireless", "nokia", 
                "hand", "mobi", "phone", "cdm", "up\.b", "audio", "SIE\-", 
                "SEC\-", "samsung", "HTC", "mot\-", "mitsu", "sagem", "sony", 
                "alcatel", "lg", "erics", "vx", "NEC", "philips", "mmm", "xx", 
                "panasonic", "sharp", "wap", "sch", "rover", "pocket", "benq", 
                "java", "pt", "pg", "vox", "amoi", "bird", "compal", "kg", 
                "voda", "sany", "kdd", "dbt", "sendo", "sgh", "gradi", "jb", 
                "\d\d\di", "moto"
            );
        
            foreach ($uamatches as $uastring) {
                if (preg_match("/".$uastring."/i", $user_agent)) {
                    return new self;
                }
            }
        
        }
        return false;
    }
    
    /**    
     * Populate the Request object.
     * 
     * @param PHPFrame_Request $request Reference to the request object.
     * 
     * @return void
     * @since  1.0
     */
    public function populateRequest(PHPFrame_Request $request) 
    {
    
        $request = array();
        
        // Get an instance of PHP Input filter
        $inputfilter = new PHPFrame_InputFilter();
            
        // Process incoming request arrays and store filtered data in class
        $request['request'] = $inputfilter->process($_REQUEST);
        $request['get']     = $inputfilter->process($_GET);
        $request['post']    = $inputfilter->process($_POST);
        
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
        $response->document(new PHPFrame_HTMLDocument());
        
        // Set response renderer
        $response->renderer(new PHPFrame_HTMLRenderer($views_path));
    }
}
