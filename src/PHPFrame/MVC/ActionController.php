<?php
/**
 * PHPFrame/MVC/ActionController.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   MVC
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * This class is used to implement the MVC (Model/View/Controller) pattern 
 * in the components.
 * 
 * As an abstract class it has to be extended to be instantiated. This class is 
 * used as a template for creating controllers when developing components. See 
 * the built in components (dashboard, user, admin, ...) for examples.
 * 
 * Controllers process requests and respond to events, typically user actions, 
 * and may invoke changes on data using the available models.
 * 
 * @category PHPFrame
 * @package  MVC
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see      PHPFrame_View
 * @since    1.0
 * @abstract 
 */
abstract class PHPFrame_ActionController extends PHPFrame_Subject
{
    /**
     * Default controller action
     * 
     * @var string
     */
    private $_default_action = null;
    /**
     * Reference to application object for which controller will execute action
     * 
     * @var PHPFrame_Application
     */
    private $_app;
    /**
     * A string containing a url to be redirected to. Leave empty for no redirection.
     *
     * @var string
     */
    private $_redirect_url = null;
    /**
     * This is a flag we use to indicate whether the controller's executed task was 
     * successful or not.
     * 
     * @var boolean
     */
    private $_success = false;
    
    /**
     * Constructor
     * 
     * @param string $default_action A string with the default action for a 
     *                               concrete action controller.
     * 
     * @return void
     * @since  1.0
     */
    public function __construct($default_action) 
    {
        // Set default action property
        $this->_default_action = (string) $default_action;
    }
    
    /**
     * This method executes a given action (invokes a named member method).
     * 
     * @param PHPFrame_Application $app
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function execute(PHPFrame_Application $app) 
    {
        $this->_app = $app;
        
        // Get action from the request
        $request_action = $app->getRequest()->getAction();
        
        // If no specific action has been requested we use default action
        if (empty($request_action)) {
            $action = $this->_default_action;
            $app->getRequest()->setAction($action);
        } else {
            $action = $request_action;
        }
        
        // Check permissions before we execute
        $controller  = $app->getRequest()->getControllerName();
        $permissions = $app->getPermissions();
        $groupid     = PHPFrame::getSession()->getGroupId();
        
        if ($permissions->authorise($controller, $action, $groupid) === true) {
            // Invoke controller action
            $this->_invokeAction($action);
        } else {
            if (!PHPFrame::getSession()->isAuth()) {
                $this->setRedirect('index.php?controller=login');
            } else {
                $this->raiseWarning('Permission denied.');
            }
        }
        
        // Redirect if set by the controller
        $this->redirect();
    }
    
    /**
     * Get reference to application object
     * 
     * @return PHPFrame_Application
     * @since  1.0
     */
    protected function app()
    {
        return $this->_app;
    }
    
    protected function request()
    {
        return $this->app()->getRequest();
    }
    
    protected function response()
    {
        return $this->app()->getResponse();
    }
    
    protected function config()
    {
        return $this->app()->getConfig();
    }
    
    protected function db()
    {
        return $this->app()->getDB();
    }
    
    protected function logger()
    {
        return $this->app()->getLogger();
    }
    
    protected function session()
    {
        return PHPFrame::getSession();
    }
    
    /**
     * Get controller's success flag
     * 
     * @access public
     * @return boolean
     * @since  1.0
     */
    public function getSuccess() 
    {
        return $this->_success;
    }
    
    /**
     * Raise error
     * 
     * @param string $msg
     * 
     * @access protected
     * @return void
     * @since  1.0
     */
    protected function raiseError($msg)
    {
        $this->_success = false;
        $this->notifyEvent($msg, PHPFrame_Subject::EVENT_TYPE_ERROR);
    }
    
    /**
     * Raise warning
     * 
     * @param string $msg
     * 
     * @access protected
     * @return void
     * @since  1.0
     */
    protected function raiseWarning($msg)
    {
        $this->_success = false;
        $this->notifyEvent($msg, PHPFrame_Subject::EVENT_TYPE_WARNING);
    }
    
    /**
     * Notify success
     * 
     * @param string $msg
     * 
     * @access protected
     * @return void
     * @since  1.0
     */
    protected function notifySuccess($msg="")
    {
        $this->_success = true;
        $this->notifyEvent($msg, PHPFrame_Subject::EVENT_TYPE_SUCCESS);
    }
    
    /**
     * Notify info
     * 
     * @param string $msg
     * 
     * @access protected
     * @return void
     * @since  1.0
     */
    protected function notifyInfo($msg)
    {
        $this->notifyEvent($msg, PHPFrame_Subject::EVENT_TYPE_INFO);
    }
    
    /**
     * Cancel and set redirect to index.
     * 
     * @access protected
     * @return void
     * @since  1.0
     */
    protected function cancel() 
    {
        $this->setRedirect('index.php');
    }
    
    /**
     * Set the redirection URL.
     *
     * @param string $url The URL we want to redirect to when we call 
     *                    PHPFrame_ActionController::redirect()
     * 
     * @access protected
     * @return void
     * @since  1.0
     */
    protected function setRedirect($url) 
    {
        $this->_redirect_url = $url;
    }
    
    /**
     * Redirect browser to redirect URL.
     * 
     * @access protected
     * @return void
     * @since  1.0
     */
    protected function redirect() 
    {
        // Get client object from session
        $client = PHPFrame::getSession()->getClient();
        
        // Check that we got the right type
        if (!$client instanceof PHPFrame_Client) {
            $msg = "Action controller could not redirect using client object";
            throw new RuntimeException($msg);
        }
        
        // Delegate redirection to client object if it is of the right type
        if (!empty($this->_redirect_url)) {
            $redirect_url = $this->_redirect_url;
            
            if (isset(self::$_instances[get_class($this)])) {
                unset(self::$_instances[get_class($this)]);
            }
            
            $client->redirect($redirect_url);
        }
    }
    
    /**
     * Get a named model
     *
     * @param string $name The model name. If empty the view name is used as default.
     * @param array  $args An array containing arguments to be passed to the Model's 
     *                     constructor.
     * 
     * @access protected
     * @return object
     * @since  1.0
     */
    protected function getModel($name, $args=array()) 
    {
        return PHPFrame_MVCFactory::getModel($name, $args);
    }
    
    /**
     * Get a named view
     *
     * @param string $name The name of the view to create.
     * 
     * @access protected
     * @return object
     * @since  1.0
     */
    protected function getView($name="")
    {
        return PHPFrame_MVCFactory::getView($name);
    }
    
    /**
     * Invoke action in concrete controller
     * 
     * This method thows an exception if the action is not supported by the 
     * controller or if any required arguments are not defined in the request.
     * 
     * @param string $action The action to inkoe in the concrete action controller
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function _invokeAction($action)
    {
        // Get reflection object for action's method
        try {
            $reflection_method = new ReflectionMethod($this, $action);
        } catch (ReflectionException $e) {
            $reflection_class  = new ReflectionClass($this);
            $parent_class      = $reflection_class->getParentClass();
            $parent_methods    = array();
            $supported_methods = array();
            
            foreach ($parent_class->getMethods() as $parent_method) {
                $parent_methods[] = $parent_method->getName();
            }
            
            foreach ($reflection_class->getMethods() as $method) {
                $is_inherited = in_array($method->getName(), $parent_methods);
                if ($method->isPublic() && !$is_inherited) {
                    $supported_methods[] = $method->getName();
                }
            }
            
            $msg  = get_class($this)." does NOT support an action called '";
            $msg .= $action."'. ".get_class($this)." supports the following ";
            $msg .= "actions: '".implode("','", $supported_methods)."'.";
            throw new BadMethodCallException($msg, 501);
        }
        
        if (!$reflection_method->isPublic()) {
            $msg  = "Action ".$action."() is defined in ".get_class($this);
            $msg .=" but its visibility is NOT public.";
            throw new LogicException($msg);
        }
        
        // Get request parameters
        $args = array();
        foreach ($reflection_method->getParameters() as $param) {
            if ($param->isDefaultValueAvailable()) {
                $default_value = $param->getDefaultValue();
            } else {
                $default_value = null;
            }
            
            $request_param = $this->app()->getRequest()->getParam(
                $param->getName(), 
                $default_value
            );
            
            // Check that required parameters are included in request
            if (!$param->isDefaultValueAvailable()) {
                if (is_null($request_param)) {
                    $msg  = "Required argument '".$param->getName()."' not ";
                    $msg .= "passed to ".get_class($this)."::".$action."().";
                    throw new BadMethodCallException($msg, 400);
                }
            }
            
            $args[$param->getName()] = $request_param;
        }
        
        // Invoke action
        $reflection_method->invokeArgs($this, $args);
    }
}
