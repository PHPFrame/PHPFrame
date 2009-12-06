<?php
/**
 * PHPFrame/MVC/MVCFactory.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   MVC
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * This class provides a number of "factory" methods used to acquire controllers, 
 * models and views.
 * 
 * @category PHPFrame
 * @package  MVC
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @see      PHPFrame_ActionController, PHPFrame_View
 * @since    1.0
 */
class PHPFrame_MVCFactory
{
    /**
     * Reference to application object
     * 
     * @var PHPFrame_Application
     */
    private static $_app;
    
    /**
     * Constructor
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function __construct() {}
    
    /**
     * Get a named action controller object
     * 
     * @param string $controller_name
     * 
     * @static
     * @access public
     * @return PHPFrame_ActionController
     * @since  1.0
     */
    public static function getActionController($controller_name)
    {
        // Create reflection object for named controller
        $controller_class = ucfirst($controller_name)."Controller";
        $reflection_obj   = new ReflectionClass($controller_class);
        
        if (!$reflection_obj->isSubclassOf("PHPFrame_ActionController")) {
            $msg  = "Action Controller not supported. ".$controller_class;
            $msg .= " does NOT extend PHPFrame_ActionController.";
            throw new LogicException($msg);
        }
        
        return $reflection_obj->newInstance();
    }
    
    /**
     * Get model
     * 
     * @param string $model_name The name of the model to get.
     * @param array  $args       An array with arguments to be passed to the
     *                           model's constructor if needed.
     * 
     * @static
     * @access public
     * @return object
     * @since  1.0
     */
    public static function getModel($model_name, $args=array()) 
    {
        $model_name = trim((string) $model_name);
        $array      = explode("/", $model_name);
        $class_name = end($array);
        
        // make a reflection object
        $reflectionObj = new ReflectionClass($class_name);
        
        // Check if class is instantiable
        if ($reflectionObj->isInstantiable()) {
            // Try to get the constructor
            $constructor = $reflectionObj->getConstructor();
            // Check to see if we have a valid constructor method
            if ($constructor instanceof ReflectionMethod) {
                // If constructor is public we create a new instance
                if ($constructor->isPublic()) {
                    return $reflectionObj->newInstanceArgs($args);
                }
            }
            // No declared constructor, so we instantiate without args
            return new $class_name;
        // If class is not instantiable we look for a "getInstance" method
        } elseif ($reflectionObj->hasMethod('getInstance')) {
            $get_instance = $reflectionObj->getMethod('getInstance');
            if ($get_instance->isPublic() && $get_instance->isStatic()) {
                $class_method_array = array($class_name, 'getInstance');
                return call_user_func_array($class_method_array, $args);
            }
        }
        
        // If we have not been able to return a model we throw an exception
        $exception_msg = $model_name." not supported. ";
        $exception_msg .= "Could not get instance of ".$class_name;
        throw new RuntimeException($exception_msg);
    }
    
    /**
     * Get a named view.
     *
     * @param string $name The name of the view to get.
     * @param array  $data Data to assign to the view.
     * 
     * @access public
     * @return PHPFrame_View
     * @since  1.0
     */
    public static function getView($name, array $data=null) 
    {
        return new PHPFrame_View($name, $data);
    }
}
