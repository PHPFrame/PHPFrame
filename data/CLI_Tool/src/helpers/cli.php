<?php
/**
 * data/CLITool/src/helpers/cli.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   PHPFrame_CLITool
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * CLI helper.
 * 
 * @category PHPFrame
 * @package  PHPFrame_CLITool
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class CliHelper extends PHPFrame_ViewHelper
{
    /**
     * Format string into 'mark down' underlined title.
     * 
     * @param string $str The string we want to format.
     * 
     * @return string
     * @since  1.0
     */
    public function formatH2($str)
    {
        $title     = (string) $str;
        $underline = "";
        
        for ($i=0; $i<strlen($title); $i++) {
            $underline .= "-";
        }
        
        return $title."\n".$underline;
    }
}
