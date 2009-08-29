<?php
/**
 * PHPFrame/Base/String.php
 * 
 * PHP version 5
 * 
 * @category PHPFrame
 * @package    PHPFrame
 * @subpackage Base
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * String Class
 * 
 * @category PHPFrame
 * @package    PHPFrame
 * @subpackage Base
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_Base_String
{
    private $_str=null;
    
    /**
     * Constructor
     * 
     * @param string $str
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct($str="")
    {
        $str = (string) trim($str);
        
        $this->_str = $str;
    }
    
    /**
     * Format a string for html output.
     * 
     * @todo This function needs to be written. It does nothing at the moment.
     * 
     * @param string $str             The string to process.
     * @param bool   $javascript_safe A boolean used to indicate whether we should
     *                                make the string safe for javascript.
     * 
     * @static
     * @access public
     * @return string
     * @since  1.0
     */
    public static function html($str, $javascript_safe=false) 
    {
        return $str;
    }
    
    /**
     * Limit string to a set number of characters
     * 
     * @param string $str               The string we want to limit.
     * @param int    $max_chars         The number of characters we want to limit to.
     * @param bool   $add_trailing_dots Boolean to indicate whether we want to add
     *                                  trailing dots or not. Default is TRUE.
     * 
     * @static
     * @access public
     * @return string
     * @since  1.0
     */
    public static function limitChars($str, $max_chars, $add_trailing_dots=true) 
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
     * @param string $str               The string we want to limit.
     * @param int    $max_chars         The number of characters we want to limit to.
     * @param bool   $add_trailing_dots Boolean to indicate whether we want to add
     *                                  trailing dots or not. Default is TRUE.
     * @static
     * @access public
     * @return string
     * @since  1.0
     */
    public static function limitWords($str, $max_chars, $add_trailing_dots=true) 
    {
        if (strlen($str) > $max_chars) {
            $str = substr($str, 0, $max_chars);
            $str = substr($str, 0, strrpos($str, " "));
            if ($add_trailing_dots === true) $str .= " ...";
        }
        
        return $str;
    }
    
    /**
     * This method is used to format a string into a string of the given length.
     * If the string is longer than the specified length it is trimmed to fit.
     * If the string is shorter than the specified length it is padded with spaces
     * on the left side to fit length.
     * 
     * @param string $str               The string to format
     * @param int    $length            The length we want to format the string to.
     * @param bool   $add_trailing_dots Boolean to indicate whether we want to add
     *                                  trailing dots or not. Default is TRUE.
     * 
     * @static
     * @access public
     * @return string
     * @since  1.0
     */
    public static function fixLength($str, $length, $add_trailing_dots=true)
    {
        // Cast input params to strict types
        $str = (string) trim($str);
        $length = (int) $length;
        $add_trailing_dots = (bool) $add_trailing_dots;
        
        if (strlen($str) > $length) {
            // Trim to fixed length
            $str = substr($str, 0, ($length-1));
            // Add trailing dots if necessary
            if ($add_trailing_dots) {
                $str = substr($str, 0, ($length-4));
                $str .= "...";
            }
        } else {
            // Add space padding
            for ($i=0; $i<($length - strlen($str)); $i++) {
                $str .= " ";
            }
        }
        
        return $str;
    }
}
