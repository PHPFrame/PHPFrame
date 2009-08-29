<?php
/**
 * PHPFrame/Mapper/DomainObjectFactory.php
 * 
 * PHP version 5
 * 
 * @category PHPFrame
 * @package    PHPFrame
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * DomainObjectFactory Class
 * 
 * @category PHPFrame
 * @package    PHPFrame
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_Mapper_DomainObjectFactory
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
        $this->_target_class = (string) trim($target_class);
    }
    
    /**
     * Create domain object
     * 
     * @param array $array
     * 
     * @access public
     * @return PHPFrame_Mapper_DomainObject
     * @since  1.0
     */
    public function createObject(array $array)
    {
        $class_name = $this->_target_class;
        
        $reflectionObj = new ReflectionClass($class_name);
        if (!$reflectionObj->isSubclassOf("PHPFrame_Mapper_DomainObject")) {
            $msg = "Domain Object '".$class_name."' not supported.";
            throw new PHPFrame_Exception($msg);
        }
        
        return $reflectionObj->newInstanceArgs(array($array));
    }
}
