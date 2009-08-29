<?php
/**
 * PHPFrame/Base/Number.php
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
 * Number Class
 * 
 * @category PHPFrame
 * @package  Base
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_Base_Number
{
    /**
     * Format bytes to human readable.
     * 
     * @param int $int The int we want to format.
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public static function bytes($int) 
    {
        $unim = array("B","KB","MB","GB","TB","PB");
        $c = 0;
        while ($int>=1024) {
            $c++;
            $int = $int/1024;
        }
        return number_format($int,($c ? 2 : 0),",",".")." ".$unim[$c];
    }
}
