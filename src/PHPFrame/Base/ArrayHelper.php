<?php
/**
 * PHPFrame/Base/ArrayHelper.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Base
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * ArrayHelper Class
 * 
 * @category PHPFrame
 * @package  Base
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_Base_ArrayHelper
{
    /**
     * Is associative array?
     * 
     * @static
     * @access public
     * @return bool
     * @since  1.0
     */
    public static function isAssoc(array $array) {
        return (is_array($array) 
                && 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));
    }
    
    public static function depth(array $array)
    {
        $depth = count($array) > 0 ? 1 : 0; 
        
        foreach ($array as $value) {
            if (is_array($value)) {
                $depth = self::depth($value) + 1;
            }
        }
        
        return (int) $depth;
    }
    
    /**
     * Convert array to string
     * 
     * @param array $array
     * 
     * @return string
     */
    public static function toString(array $array)
    {
        $str = "";
        
        if (!self::isAssoc($array)) {
            $str = implode(", ", $array);
        }
        
        foreach ($array as $key=>$value) {
            $str .= $key." => ".$value;
        }
        
        return $str;
    }
    
    /**
     * Convert array to XML
     * 
     * @param array $array
     * 
     * @return string
     */
    public static function toXML(array $array)
    {
        //...
        throw new PHPFrame_Exception("FIX ME!!!!!!");
    }
}
