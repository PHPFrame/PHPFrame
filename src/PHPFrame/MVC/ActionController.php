<?php
/**
 * PHPFrame/MVC/ActionController.php
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
 * Action Controller class
 * 
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
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage MVC
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see        PHPFrame_MVC_Model, PHPFrame_MVC_View
 * @since      1.0
 * @abstract 
 */
abstract class PHPFrame_MVC_ActionController
{
    /**
     * Instances of its concrete children
     * 
     * @var array of objects of type PHPFrame_MVC_ActionController
     */
    private static $_instances=array();
    /**
     * Default controller action
     * 
     * @var    string
     */
    protected $default_action=null;
    /**
     * A string containing a url to be redirected to. Leave empty for no redirection.
     *
     * @var string
     */
    protected $redirect_url=null;
    /**
     * A reference to the System Events object.
     * 
     * This object is used to report system messages from the action controllers.
     * 
     * @var    object
     */
    protected $sysevents=null;
    /**
     * This is a flag we use to indicate whether the controller's executed task was 
     * successful or not.
     * 
     * @var boolean
     */
    protected $success=false;
    
    /**
     * Constructor
     * 
     * @param string $default_action A string with the default action for a 
     *                               concrete action controller.
     *                                       
     * @return   void
     * @since    1.0
     */
    protected function __construct($default_action) 
    {
        $this->default_action = (string) $default_action;
        
        // Get reference to System Events object
        $this->sysevents = PHPFrame::Session()->getSysevents();
    }
    
    /**
     * Get Instance
     * 
     * @param string $component_name A string with the name of the concrete
     *                               action controller.
     * 
     * @return PHPFrame_MVC_ActionController
     * @since  1.0
     */
    public static function getInstance($component_name) 
    {
        $class_name = substr($component_name, 4)."Controller";
        
        $is_set = isset(self::$_instances[$class_name]);
        $is_type = @(self::$_instances[$class_name] instanceof self);
        if (!$is_set || !$is_type) {
            self::$_instances[$class_name] = new $class_name;
        }
        
        return self::$_instances[$class_name];
    }
    
    /**
     * Execute action
     * 
     * This method executes a given task (runs a named member method).
     *
     * @return void
     * @since  1.0
     */
    public function execute() 
    {
        // Get action from the request
        $request_action = PHPFrame::Request()->getAction();
        //echo $request_action; exit;
        // If no specific action has been requested we use default action
        if (empty($request_action)) {
            $action = $this->default_action;
        } else {
            $action = $request_action;
        }
        
        // Check permissions before we execute
        $component = PHPFrame::Request()->getComponentName();
        $groupid = PHPFrame::Session()->getGroupId();
        $permissions = PHPFrame::AppRegistry()->getPermissions();
        if ($permissions->authorise($component, $action, $groupid) === true) {
            if (is_callable(array($this, $action))) {
                $this->$action();
            } else {
                $exception_msg = "Action ".$action."() not found in controller.";
                throw new PHPFrame_Exception($exception_msg);
            }
        } else {
            if (!PHPFrame::Session()->isAuth()) {
                $this->setRedirect('index.php?component=com_login');
            } else {
                $this->sysevents->setSummary('Permission denied.');
            }
        }
        
        // Redirect if set by the controller
        $this->redirect();
    }
    
    /**
     * Get controller's success flag
     * 
     * @return boolean
     * @since  1.0
     */
    public function getSuccess() 
    {
        return $this->success;
    }
    
    /**
     * Cancel
     * 
     * Cancel and set redirect to index.
     *
     * @return void
     * @since  1.0
     */
    protected function cancel() 
    {
        $this->setRedirect('index.php');
    }
    
    /**
     * Set redirection url
     * 
     * Set the redirection URL.
     *
     * @param string $url The URL we want to redirect to when we call 
     *                    PHPFrame_MVC_ActionController::redirect()
     * 
     * @return void
     * @since  1.0
     */
    protected function setRedirect($url) 
    {
        $this->redirect_url = PHPFrame_Utils_Rewrite::rewriteURL($url, false);
    }
    
    /**
     * Redirect
     * 
     * Redirect browser to redirect URL.
     * 
     * @return void
     * @since  1.0
     */
    protected function redirect() 
    {
        $client_name = PHPFrame::Session()->getClientName();
        if ($this->redirect_url && $client_name != "cli") {
            header("Location: ".$this->redirect_url);
            exit;
        }
    }
    
    /**
     * Get model
     * 
     * Gets a named model within the component.
     *
     * @param string $name The model name. If empty the view name is used as default.
     * @param array  $args An array containing arguments to be passed to the Model's 
     *                     constructor.
     * 
     * @return object
     * @since  1.0
     */
    protected function getModel($name, $args=array()) 
    {
        $component_name = PHPFrame::Request()->getComponentName();
        return PHPFrame_MVC_Factory::getModel($component_name, $name, $args);
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
     * @return object
     * @since  1.0
     */
    protected function getView($name, $layout=null)
    {
        return PHPFrame_MVC_Factory::getView($name, $layout);
    }
}
