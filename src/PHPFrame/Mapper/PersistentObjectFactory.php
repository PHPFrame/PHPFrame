<?php
/**
 * PHPFrame/Mapper/PersistentObjectFactory.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Mapper
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Persistent Object Factory Class
 * 
 * @category PHPFrame
 * @package  Mapper
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_PersistentObjectFactory
{
    /**
     * Constructor
     * 
     * @param string $target_class
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct($target_class)
    {
        $this->_target_class = trim((string) $target_class);
    }
    
    /**
     * Create persistent object
     * 
     * @param array $array
     * 
     * @access public
     * @return PHPFrame_PersistentObject
     * @since  1.0
     */
    public function createObject(array $array)
    {
        $class_name = $this->_target_class;
        
        $reflectionObj = new ReflectionClass($class_name);
        if (!$reflectionObj->isSubclassOf("PHPFrame_PersistentObject")) {
            $msg = "Domain Object '".$class_name."' not supported.";
            throw new RuntimeException($msg);
        }
        
        return $reflectionObj->newInstanceArgs(array($array));
    }
}
