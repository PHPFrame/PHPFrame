<?php
/**
 * PHPFrame/Base/Singleton.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Base
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Singleton Class
 * 
 * This class is used to inherit the Singleton design pattern.
 * It restricts instantiation of a class to one object. 
 * 
 * Singleton objects are instantiated using the getInstance() method. 
 * This is enforced with the __construct() method set to protected.
 * 
 * Example:
 * <code>
 * class singletonClass extends PHPFrame_Base_Singleton {
 *         // Class code...
 * }
 * 
 * // This will fail
 * $mySingletonObject1 = new singletonClass();
 * 
 * // This will assign the singleton object to $mySingletonObject2
 * $mySingletonObject2 = PHPFrame_Base_Singleton::getInstance('singletonClass');
 * 
 * // This will also work, because the getInstance() method is inherited from the PHPFrame_Base_Singleton class
 * $mySingletonObject3 = singletonClass::getInstance('singletonClass');
 * </code>
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Base
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 * @abstract
 */
abstract class PHPFrame_Base_Singleton extends PHPFrame_Base_Object
{
    /**
     * Variable holding an array of "single" instances of this classes children.
     * 
     * @var array
     */
    private static $_instances=array();
    
    /**
     * Constructor
     * 
     * The protected constructor ensures this class is not instantiated using the 
     * 'new' keyword. Singleton objects are instantiated using the getInstance() 
     * method.
     * 
     * @return void
     */
    protected function __construct() {}
    
    /**
     * Get the single instance of this sigleton class.
     * 
     * The $class_name parameter is used in order to create the instance 
     * using the class name from where the method is called at run level.
     * If $class_name is empty this method returns an instance of its own,
     * not the child class that inherited this method.
     * 
     * @param string $class_name The class name to instantiate.
     * @param array  $params     Parameters to be passed to new instance constructor
     * 
     * @static
     * @access public
     * @return object
     * @since  1.0
     */
    public static function getInstance($class_name, $params=array()) {
        // Check whether the requested class has alreay been instantiated
        if (!array_key_exists($class_name, self::$_instances)) {
            // instance does not exist, so create it
            if (class_exists($class_name)) {
                //self::$_instances[$class_name] = new $class_name;
                eval(
                    'self::$_instances[$class_name] = new $class_name('
                    .implode(",", $params)
                    .');'
                );
            }
            else {
                throw new Exception('Class '.$class_name.' not found.');
            }
        }
        
        return self::$_instances[$class_name];
    }
    
    /**
     * Unset a singleton object
     * 
     * @param string $class_name
     * 
     * @static
     * @access public
     * @return void
     * @since  1.0
     */
    public static function destroyInstance($class_name) {
           // Check whether the requested class has alreay been instantiated
        if (array_key_exists($class_name, self::$_instances)) {
            // unset instance
            unset(self::$_instances[$class_name]);
        }
        else {
            throw new Exception('No instance of '.$class_name.' found.');
        }
    } 
}
