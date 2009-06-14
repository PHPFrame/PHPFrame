<?php
/**
 * PHPFrame/MVC/Factory.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage MVC
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * MVC Factory Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage MVC
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see        PHPFrame_MVC_ActionController, PHPFrame_MVC_View
 * @since      1.0
 */
class PHPFrame_MVC_Factory
{
    /**
     * Constructor
     * 
     * @access private
     * @return void
     */
    private function __construct() {}
    
    /**
     * Get model
     * 
     * @param string $component_name The name of the container component of the 
     *                               model we want to get (ie: com_login).
     * @param string $model_name     The name of the model to get.
     * @param array  $args           An array with arguments to be passed to the
     *                               model's constructor if needed.
     * 
     * @return PHPFrame_MVC_Model
     * @since  1.0
     * @todo   Have to add type checking using instanceof operator to guarantee that 
     *         we return an object of type PHPFrame_MVC_Model
     */
    public static function getModel($component_name, $model_name, $args=array()) 
    {
        $class_name = substr($component_name, 4)."Model";
        $class_name .= ucfirst($model_name);
        
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
        } elseif ($reflectionObj->hasMethod('getInstance')) {
            $get_instance = $reflectionObj->getMethod('getInstance');
            if ($get_instance->isPublic() && $get_instance->isStatic()) {
                $class_method_array = array($class_name, 'getInstance');
                return call_user_func_array($class_method_array, $args);
            }
        }
        
        // If we have not been able to return a model object we throw an exception
        $exception_msg = $model_name." not supported. ";
        $exception_msg .= "Could not get instance of ".$class_name;
        throw new PHPFrame_Exception($exception_msg);
    }
    
    /**
     * Get view
     * 
     * Get a named view within the component.
     *
     * @param string $name   The name of the view to create.
     * @param string $layout A specific layout to use for the view. This argument is 
     *                       optional.
     * 
     * @return PHPFrame_MVC_View
     * @since  1.0
     */
    public static function getView($name, $layout='') 
    {
        $component_name = PHPFrame::Request()->getComponentName();
        $class_name = strtolower(substr($component_name, 4));
        $class_name .= "View".ucfirst($name);
        
        try {
            $reflection_obj = new ReflectionClass($class_name);
        }
        catch (Exception $e) {
            throw new PHPFrame_Exception($e->getMessage());
        }
        
        $reflection_abstract_view = new ReflectionClass("PHPFrame_MVC_View");
        if ($reflection_obj->isSubclassOf($reflection_abstract_view)) {
            return new $class_name($layout);
        } else {
            $exception_msg = "View class '".$class_name."' not supported.";
            throw new PHPFrame_Exception($exception_msg);
        }
    }
}