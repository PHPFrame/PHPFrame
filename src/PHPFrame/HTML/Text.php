<?php
/**
 * @version       SVN: $Id$
 * @package       PHPFrame
 * @subpackage    html
 * @copyright     2009 E-noise.com Limited
 * @license       http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Text Class
 * 
 * @package        PHPFrame
 * @subpackage     html
 * @since         1.0
 */
class PHPFrame_HTML_Text 
{
    /**
     * Format a string for html output.
     * 
     * @todo    This function needs to be written. It does nothing at the moment.
     * @param    string    $str
     * @param    bool    $javascript_safe
     * @return    string
     */
    public static function _($str, $javascript_safe=false) 
    {
        return $str;
    }
    
    /**
     * Format bytes to human readable.
     * 
     * @param    string    $str
     * @return    string
     * @since     1.0
     */
    public static function bytes($str) 
    {
        $unim = array("B","KB","MB","GB","TB","PB");
        $c = 0;
        while ($str>=1024) {
            $c++;
            $str = $str/1024;
        }
        return number_format($str,($c ? 2 : 0),",",".")." ".$unim[$c];
    }
    
    /**
     * Limit string to a set number of characters
     * 
     * @param    string    $str
     * @param    int        $max_chars
     * @param     boolean    $add_trailing_dots
     * @return    string
     */
    public static function limit_chars($str, $max_chars, $add_trailing_dots=true) 
    {
        if (strlen($str) > $max_chars) {
            $str = substr($str, 0, $max_chars);
            if ($add_trailing_dots === true) {
                // Remove another 4 chars to replace with dots
                $str = substr($str, 0, (strlen($str)-4));
                $str .= " ...";
            }
        }
        
        return $str;
    }
    
    /**
     * Limit the number of words.
     * 
     * @param    string    $str
     * @param    int        $max_chars
     * @param    bool    $add_trailing_dots
     * @return    string
     */
    public static function limit_words($str, $max_chars, $add_trailing_dots=true) 
    {
        if (strlen($str) > $max_chars) {
            $str = substr($str, 0, $max_chars);
            $str = substr($str, 0, strrpos($str, " "));
            if ($add_trailing_dots === true) $str .= " ...";
        }
        
        return $str;
    }
}
