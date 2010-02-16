<?php
/**
 * PHPFrame/Utils/InputFilter.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Utils
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * InputFilter class.
 * 
 * @category PHPFrame
 * @package  Utils
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since    1.0
 */
class PHPFrame_InputFilter
{
	/**
	 * Array with tags to blacklist.
	 * 
	 * @var array
	 */
    private $_tag_blacklist  = array(
        'applet', 'body', 'bgsound', 'base', 'basefont', 'embed', 'frame', 
        'frameset', 'head', 'html', 'id', 'iframe', 'ilayer', 'layer', 'link', 
        'meta', 'name', 'object', 'script', 'style', 'title', 'xml'
    );
    /**
     * Array with attributes to blacklist.
     * 
     * @var array
     */
    private $_attr_blacklist = array(
        'action', 'background', 'codebase', 'dynsrc', 'lowsrc'
    );
    
    // should also strip ALL event handlers
    
    /**
     * Constructor.
     * 
     * @return void
     * @since  1.0
     */
    public function __construct()
    {
        //...
    }
    
    /**
     * Process/filter a value.
     * 
     * @param string $var The value to filter.
     * 
     * @return void
     * @since  1.0
     */
    public function process($var)
    {
        return $var;
    }
}
