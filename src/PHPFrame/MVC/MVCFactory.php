<?php
/**
 * PHPFrame/MVC/MVCFactory.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   MVC
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * This class provides a number of "factory" methods used to acquire controllers, 
 * models and views.
 * 
 * @category PHPFrame
 * @package  MVC
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see      PHPFrame_ActionController, PHPFrame_View
 * @since    1.0
 */
class PHPFrame_MVCFactory
{
    /**
     * Constructor
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function __construct() {}
    
    /**
     * Magic method to autoload MVC classes
     * 
     * This autoloader is registered in {@link PHPFrame_FrontController::run()}.
     * 
     * @param string $class_name
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public static function __autoload($class_name)
    {
        $file_path = PHPFRAME_INSTALL_DIR.DS."src".DS;
        
        $super_classes = array("Controller", "Helper", "Lang");
        foreach ($super_classes as $super_class) {
            if (preg_match('/'.$super_class.'$/', $class_name)) {
                break;
            }
        }
        
        // Set base path for objects of given superclass
        $file_path .= strtolower($super_class);
        
        // Append lang dir for lang classes
        if ($super_class == "Lang") {
            $file_path .= DS.PHPFrame::Config()->get("default_lang");
        // Append 's' to dir name except for all others
        } else {
            $file_path .= "s";
            
        }
        
        // Remove superclass name from class name
        $class_name = str_replace($super_class, "", $class_name);
            
        // Build dir path by breaking camel case class name
        $pattern = '/[A-Z]{1}[a-zA-Z0-9]+/';
        $matches = array();
        preg_match_all($pattern, ucfirst($class_name), $matches);
        if (isset($matches[0]) && is_array($matches[0])) {
            $file_path .= DS.strtolower(implode(DS, $matches[0]));
        }
    
        // Append file extension
        $file_path .= ".php";
        
        // require the file if it exists
        if (is_file($file_path)) {
            @include $file_path;
            return;
        }
    }
    
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
            $msg  = "Action Controller not supported. ";
            $msg .= $controller_class." does NOT extend PHPFrame_ActionController.";
            throw new LogicException($msg);
        }
        
        return PHPFrame_ActionController::getInstance($controller_name);
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
        
        if (!class_exists($class_name)) {
            $file_name  = PHPFRAME_INSTALL_DIR.DS."src".DS."models";
            $file_name .= DS.strtolower($model_name).".php";
            
            if (is_file($file_name)) {
                @include $file_name;
            }
        }
        
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
        // If class is not instantiable we look for a method called "getInstance"
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
        throw new RuntimeException($exception_msg);
    }
    
    /**
     * Get a named view.
     *
     * @param string $name The name of the view to get.
     * 
     * @access public
     * @return PHPFrame_View
     * @since  1.0
     */
    public static function getView($name) 
    {
        return new PHPFrame_View($name);
    }
}