<?php
/**
 * @version       SVN: $Id$
 * @package       PHPFrame
 * @subpackage    client
 * @copyright     2009 E-noise.com Limited
 * @license       http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
    
/**
 * Client for Mobile Devices
 * 
 * @package        PHPFrame
 * @subpackage     client
 * @since         1.0    
 */
class PHPFrame_Client_Mobile implements PHPFrame_Client_IClient 
{
        
    /**
     * Check if this is the correct helper for the client being used
     * 
     * @static
     * @access    public
     * @return    PHPFrame_Client_IClient|boolean    Object instance of this class if correct helper for client or false otherwise.
     */
    public static function detect() 
    {
        
        if (isset($_SERVER["HTTP_X_WAP_PROFILE"])) {
            return new self;
        }
        
        if (preg_match("/wap\.|\.wap/i",$_SERVER["HTTP_ACCEPT"])) { 
            return new self;
        }
        
        if (isset($_SERVER["HTTP_USER_AGENT"])) {
        
            if (preg_match("/Creative\ AutoUpdate/i",$_SERVER["HTTP_USER_AGENT"])) {
                return new self;
            }
        
            $uamatches = array("midp", "j2me", "avantg", "docomo", "novarra", "palmos", "palmsource", "240x320", "opwv", "chtml", "pda", "windows\ ce", "mmp\/", "blackberry", "mib\/", "symbian", "wireless", "nokia", "hand", "mobi", "phone", "cdm", "up\.b", "audio", "SIE\-", "SEC\-", "samsung", "HTC", "mot\-", "mitsu", "sagem", "sony", "alcatel", "lg", "erics", "vx", "NEC", "philips", "mmm", "xx", "panasonic", "sharp", "wap", "sch", "rover", "pocket", "benq", "java", "pt", "pg", "vox", "amoi", "bird", "compal", "kg", "voda", "sany", "kdd", "dbt", "sendo", "sgh", "gradi", "jb", "\d\d\di", "moto");
        
            foreach ($uamatches as $uastring) {
                if (preg_match("/".$uastring."/i",$_SERVER["HTTP_USER_AGENT"])) {
                    return new self;
                }
            }
        
        }
        return false;
    }
    
    /**    
     * Populate the Unified Request array
     * 
     * @access    public
     * @return    array    Unified Request Array
     */
    public function populateURA() 
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
     * Get helper name
     * 
     * @access    public
     * @return    string    Name to identify helper type
     */
    public function getName() 
    {
        return "mobile";
    }
    
    /**
     * Pre action hook
     * 
     * This method is invoked by the front controller before invoking the requested
     * action in the action controller. It gives the client an opportunity to do 
     * something before the component is executed.
     * 
     * @return    void
     */
    public function preActionHook() {}
    
    /**
     * Render component view
     * 
     * This method is invoked by the views and renders the ouput data in the format specified
     * by the client.
     * 
     * @param    array    $data    An array containing the data assigned to the view.
     * @return    void
     */
    public function renderView($data) {}
    
    /**
     * Render overall template
     *
     * @param    string    &$str    A string containing the component output.
     * @return    void
     */
    public function renderTemplate(&$str) {}
}
