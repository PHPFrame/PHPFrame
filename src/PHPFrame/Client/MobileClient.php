<?php
/**
 * PHPFrame/Client/MobileClient.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Client
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 * @ignore
 */

/**
 * Client for Mobile Devices
 * 
 * @category PHPFrame
 * @package  Client
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 * @ignore
 */
class PHPFrame_MobileClient extends PHPFrame_DefaultClient
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
}
