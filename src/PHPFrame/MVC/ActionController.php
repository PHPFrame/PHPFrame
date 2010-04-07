<?php
/**
 * PHPFrame/MVC/ActionController.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   MVC
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
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
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
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
     * A string containing a url to be redirected to. Leave empty for no
     * redirection.
     *
     * @var string
     */
    private $_redirect_url = null;
    /**
     * This is a flag we use to indicate whether the controller's executed task
     * was successful or not.
     *
     * @var boolean
     */
    private $_success = true;

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
     * @param PHPFrame_Application $app Reference to application object.
     *
     * @return void
     * @since  1.0
     */
    public function execute(PHPFrame_Application $app)
    {
        $this->_app = $app;

        // Get action from the request
        $request_action = $app->request()->action();

        // If no specific action has been requested we use default action
        if (empty($request_action)) {
            $action = $this->_default_action;
            $app->request()->action($action);
        } else {
            $action = $request_action;
        }

        $this->_invokeAction($action);

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
        return $this->_success;
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

    /**
     * Get reference to application's config object.
     *
     * @return PHPFrame_Config
     * @since  1.0
     */
    protected function config()
    {
        return $this->app()->config();
    }

    /**
     * Get reference to application's request object.
     *
     * @return PHPFrame_Request
     * @since  1.0
     */
    protected function request()
    {
        return $this->app()->request();
    }

    /**
     * Get reference to application's response object.
     *
     * @return PHPFrame_Response
     * @since  1.0
     */
    protected function response()
    {
        return $this->app()->response();
    }

    /**
     * Get reference to application's registry object.
     *
     * @return PHPFrame_FileRegistry
     * @since  1.0
     */
    protected function registry()
    {
        return $this->app()->registry();
    }

    /**
     * Get reference to application's database object.
     *
     * @return PHPFrame_Database
     * @since  1.0
     */
    protected function db()
    {
        return $this->app()->db();
    }

    /**
     * Get reference to application's mailer object.
     *
     * @return PHPFrame_Mailer
     * @since  1.0
     */
    protected function mailer()
    {
        return $this->app()->mailer();
    }

    /**
     * Get reference to application's IMAP object.
     *
     * @return PHPFrame_FileRegistry
     * @since  1.0
     */
    protected function imap()
    {
        return $this->app()->imap();
    }

    /**
     * Get reference to application's logger object.
     *
     * @return PHPFrame_Logger
     * @since  1.0
     */
    protected function logger()
    {
        return $this->app()->logger();
    }

    /**
     * Get reference to application's session object.
     *
     * @return PHPFrame_SessionRegistry
     * @since  1.0
     */
    protected function session()
    {
        return $this->app()->session();
    }

    /**
     * Get reference to session's user object.
     *
     * @return PHPFrame_User
     * @since  1.0
     */
    protected function user()
    {
        return $this->app()->user();
    }

    /**
     * Get a named view
     *
     * @param string $name The name of the view to create.
     * @param array  $data Data to assign to the view.
     *
     * @return PHPFrame_View
     * @since  1.0
     */
    protected function view($name="", array $data=null)
    {
        return $this->app()->factory()->view($name, $data);
    }

    /**
     * Get a named view helper.
     *
     * @param string $name The name of the helper to create.
     *
     * @return PHPFrame_ViewHelper
     * @since  1.0
     */
    protected function helper($name)
    {
        return $this->app()->factory()->getViewHelper($name);
    }

    /**
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
     * Set the redirection URL.
     *
     * @param string $url The URL we want to redirect to when we call
     *                    PHPFrame_ActionController::redirect()
     *
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
     * @return void
     * @since  1.0
     * @todo   Rewrite URL using plugin
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
            //echo $this->_redirect_url; exit;
            //$url = PHPFrame_URLRewriter::rewriteURL($url);
            $client->redirect($this->_redirect_url);
        }
    }

    /**
     * Raise error
     *
     * @param string $msg The error message.
     *
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
     * @param string $msg The warning message.
     *
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
     * @param string $msg The success message.
     *
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
     * @param string $msg The info message.
     *
     * @return void
     * @since  1.0
     */
    protected function notifyInfo($msg)
    {
        $this->notifyEvent($msg, PHPFrame_Subject::EVENT_TYPE_INFO);
    }

    /**
     * Invoke action in concrete controller
     *
     * This method thows an exception if the action is not supported by the
     * controller or if any required arguments are not defined in the request.
     *
     * @param string $action The action to invoke in the action controller.
     *
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
            throw new BadMethodCallException($msg, 404);
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

            $request_param = $this->app()->request()->param(
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
