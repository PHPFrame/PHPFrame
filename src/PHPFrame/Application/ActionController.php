<?php
/**
 * PHPFrame/Application/ActionController.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Application
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
 * @subpackage Application
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see        PHPFrame_Application_Model, PHPFrame_Application_View
 * @since      1.0
 * @abstract 
 */
abstract class PHPFrame_Application_ActionController
{
    /**
     * Instances of its concrete children
     * 
     * @var array of objects of type PHPFrame_Application_ActionController
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
        $this->sysevents = PHPFrame::getSysevents();
        
        $component_name = PHPFrame::Request()->getComponentName();
        $components = PHPFrame::getComponents();
        $this->component_info = $components->loadByOption($component_name);
        
        // Add pathway item
        $pathway_item_name = ucwords($this->component_info->name);
        $pathway_item_url = "index.php?component=com_";
        $pathway_item_url .= $this->component_info->name;
        PHPFrame::getPathway()->addItem($pathway_item_name, $pathway_item_url);
        
        // Append component name in ducument title
        $document = PHPFrame::getDocument('html');
        if (!empty($document->title)) {
            $document->title .= ' - ';
        }
        $document->title .= ucwords($this->component_info->name);
    }
    
    /**
     * Get Instance
     * 
     * @param string $class_name A string with the name of the concrete action 
     *                           controller.
     * 
     * @return PHPFrame_Application_ActionController
     * @since  1.0
     */
    public static function getInstance($class_name) 
    {
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
        $permissions = PHPFrame::getPermissions();
        if ($permissions->authorise($component, $action, $groupid) === true) {
            if (is_callable(array($this, $action))) {
                // Start buffering
                ob_start();
                $this->$action();
                // save buffer in response object
                $action_output = ob_get_contents();
                // clean output buffer
                ob_end_clean();
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
        
        // Return action's output as string
        return $action_output;
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
     *                    PHPFrame_Application_ActionController::redirect()
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
        return PHPFrame::getModel($component_name, $name, $args);
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
    protected function getView($name, $layout='') 
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
        
        $reflection_abstract_view = new ReflectionClass("PHPFrame_Application_View");
        if ($reflection_obj->isSubclassOf($reflection_abstract_view)) {
            return new $class_name($layout);
        } else {
            $exception_msg = "View class '".$class_name."' not supported.";
            throw new PHPFrame_Exception($exception_msg);
        }
    }
}
