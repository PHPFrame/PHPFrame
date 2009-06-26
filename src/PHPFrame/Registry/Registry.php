<?php
/**
 * PHPFrame/Registry/Registry.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Registry
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Abstract Registry Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Registry
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 * @abstract
 */
abstract class PHPFrame_Registry extends PHPFrame_Base_StdObject
{
    /**
     * Constructor
     * 
     * @access    protected
     * @return    void
     * @since    1.0
     */
    abstract protected function __construct();
    
    /**
     * Get Instance
     * 
     * @static
     * @access    public
     * @return     PHPFrame_Registry
     * @since    1.0
     */
    abstract public static function getInstance();
    
    /**
     * Get a registry variable
     * 
     * @access    public
     * @param    string    $key
     * @param    mixed    $default_value
     * @return    mixed
     * @since    1.0
     */
    abstract public function get($key, $default_value=null);
    
    /**
     * Set a registry variable
     * 
     * @access    public
     * @param    string    $key
     * @param    mixed    $value
     * @return    void
     * @since    1.0
     */
    abstract public function set($key, $value);
}
