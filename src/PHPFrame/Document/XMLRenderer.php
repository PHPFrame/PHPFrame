<?php
/**
 * PHPFrame/Document/XMLRenderer.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Document
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * XML renderer class
 * 
 * @category PHPFrame
 * @package  Document
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @see      PHPFrame_IRenderer
 * @since    1.0
 */
class PHPFrame_XMLRenderer implements PHPFrame_IRenderer
{
    /**
     * Render a given value.
     * 
     * @param mixed $value The value we want to render.
     * 
     * @return void
     * @since  1.0
     */
    public function render($value)
    {
    	if (!is_array($value) && !is_object($value)) {
    	    return (string) $value;
    	}
    	
        return PHPFrame_XMLSerialiser::serialise($value);
    }
}
