<?php
/**
 * PHPFrame/Registry/Registry.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Registry
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Abstract Registry Class
 * 
 * @category PHPFrame
 * @package  Registry
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 * @abstract
 */
abstract class PHPFrame_Registry
{
    /**
     * Constructor
     * 
     * @access protected
     * @return void
     * @since  1.0
     */
    abstract protected function __construct();
    
    /**
     * Get a registry variable
     * 
     * @param string $key
     * @param mixed  $default_value
     * 
     * @access public
     * @return mixed
     * @since  1.0
     */
    abstract public function get($key, $default_value=null);
    
    /**
     * Set a registry variable
     * 
     * @param string $key
     * @param mixed  $value
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    abstract public function set($key, $value);
}
