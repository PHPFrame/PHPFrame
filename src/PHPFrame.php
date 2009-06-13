<?php
/**
 * PHPFrame/PHPFrame.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Main
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * PHPFrame Class
 * 
 * This class provides a number of static methods that serve as a simplified
 * interface or facade to the PHPFrame framework.
 * 
 * It also provides information about the installed PHPFrame version.
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Main
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame
{
    /**
     * The PHPFrame version
     * 
     * @var string
     */
    const VERSION='1.0 Alpha';
    
    /**
     * Get PHPFrame version
     * 
     * @return string
     * @since  1.0
     */
    public static function Version() 
    {
        return self::VERSION;
    }
    
    /**
     * Fire up the app
     * 
     * This method instantiates the front controller and runs it.
     * 
     * @return void
     * @since  1.0
     */
    public static function Fire() 
    {
        $frontcontroller = new PHPFrame_Application_FrontController();
        $frontcontroller->run();
    }
    
    /**
     * Get component action controller object for given option
     * 
     * @param string $component_name The name of the concrete action controller
     *                               to get (ie: com_login).
     * 
     * @return PHPFrame_MVC_ActionController
     * @since  1.0
     */
    public static function getActionController($component_name) 
    {
        $class_name = substr($component_name, 4)."Controller";
        return PHPFrame_MVC_ActionController::getInstance($class_name);
    }
    
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
     * Request Registry
     * 
     * @return PHPFrame_Registry_Request
     * @since  1.0
     */
    public static function Request() 
    {
        return PHPFrame_Registry_Request::getInstance();
    }
    
    /**
     * Get session object
     * 
     * @return PHPFrame_Registry_Session
     * @since  1.0
     */
    public static function Session() 
    {
        return PHPFrame_Registry_Session::getInstance();
    }
    
    /**
     * Get application registry
     * 
     * @return PHPFrame_Registry_Application
     * @since  1.0
     */
    public static function AppRegistry() 
    {
        return PHPFrame_Registry_Application::getInstance();
    }
    
    /**
     * Get database object
     * 
     * @param object $dsn     An object of type PHPFrame_Database_DSN used to get DB 
     *                        connection. This parameter is optional. If omitted a 
     *                        new DSN object will be created using the database 
     *                        details provided by the config class. 
     * @param string $db_user If we specify a DSN object we might also need to 
     *                        provide a db user in order to connect to the database 
     *                        server.
     * @param string $db_pass When both a DSN object and a db user have been passed 
     *                        we might also need to provide a password for the db 
     *                        connection.
     * 
     * @return PHPFrame_Database
     * @since  1.0
     */
    public static function DB(
        PHPFrame_Database_DSN $dsn=null,
        $db_user=null,
        $db_pass=null
    ) {
        // If no DSN is passed we use settings from config
        if (is_null($dsn)) {
            $dsn_concrete_class = "PHPFrame_Database_DSN_".config::DB_DRIVER;
            $dsn = new $dsn_concrete_class(config::DB_HOST, config::DB_NAME);
        }
        
        if (is_null($db_user)) {
            $db_user = config::DB_USER;
        }
        
        if (is_null($db_pass)) {
            $db_pass = config::DB_PASS;
        }
        
        return PHPFrame_Database::getInstance($dsn, $db_user, $db_pass);
    }
    
    /**
     * Get document object
     * 
     * @param string $type The document type (html or xml)
     * 
     * @return PHPFrame_Document
     * @since  1.0
     */
    public static function getDocument($type) 
    {
        $concrete_document = 'PHPFrame_Document_'.strtoupper($type);
        return PHPFrame_Base_Singleton::getInstance($concrete_document);
    }
    
    /**
     * Get uri object
     * 
     * @param string $uri The URI used to construct the URI object. This parameter is
     *                    optional. If left blank or not passed a value the URI 
     *                    object will be instantiated using the current URI.
     * 
     * @return PHPFrame_Utils_URI
     * @since  1.0
     */
    public static function getURI($uri='') 
    {
        return new PHPFrame_Utils_URI($uri);
    }
    
    /**
     * Get pathway object
     * 
     * @return PHPFrame_Application_Pathway
     * @since  1.0
     */
    public static function getPathway() 
    {
        return PHPFrame_Base_Singleton::getInstance('PHPFrame_Application_Pathway');
    }
}
