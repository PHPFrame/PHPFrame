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
    private $_def_action = null;
    /**
     * Reference to application object for which controller will execute action
     *
     * @var PHPFrame_Application
     */
    private $_app;

    /**
     * Constructor
     *
     * @param PHPFrame_Application $app        Reference to application object.
     * @param string               $def_action A string with the default action
     *                                         for a concrete controller.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Application $app, $def_action)
    {
        $this->_app = $app;
        $this->_def_action = (string) $def_action;
    }

    /**
     * This method executes a given action (invokes a named member method).
     *
     * @return void
     * @since  1.0
     */
    public function execute()
    {
        // Get action from the request
        $request_action = $this->app()->request()->action();

        // If no specific action has been requested we use default action
        if (empty($request_action)) {
            $action = $this->_def_action;
            $this->app()->request()->action($action);
        } else {
            $action = $request_action;
        }

        $this->_invokeAction($action);
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
     * Get reference to application's crypt object.
     *
     * @return PHPFrame_Crypt
     * @since  1.0
     */
    protected function crypt()
    {
        return $this->app()->crypt();
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
     * Set redirection location.
     *
     * @param string $location    URL to redirect to after action has been
     *                            executed.
     * @param int    $status_code [Optional] Default value is 303.
     *
     * @return void
     * @since  1.0
     */
    public function setRedirect($location, $status_code=303)
    {
        $this->response()->header("Location", trim((string) $location));
        $this->response()->statusCode($status_code);
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
     * Check session token passed in request.
     *
     * @return void
     * @since  1.2
     * @throws Exception
     */
    protected function checkToken()
    {
        $session_token = $this->session()->getToken();
        $request_token = base64_decode($this->request()->param("token", null));

        if ($session_token !== $request_token) {
            $msg = "Permission denied.";
            throw new Exception($msg, 401);
        }
    }

    /**
     * Fetch persistent object by ID and check read access.
     *
     * @param PHPFrame_Mapper $mapper The persistent object mapper.
     * @param int             $id The user id.
     * @param bool            $w [Optional] Ensure write access? Default is FALSE.
     *
     * @return PHPFrame_PersistentObject
     * @since  1.0
     */
    protected function fetchObj(PHPFrame_Mapper $mapper, $id, $w=false)
    {
        $target_class = $mapper->getFactory()->getTargetClass();

        $id = filter_var($id, FILTER_VALIDATE_INT);
        if ($id === false) {
            throw new Exception("Invalid ".$target_class." id.", 400);
        }

        $obj = $mapper->findOne($id);

        if (!$obj instanceof $target_class) {
            throw new Exception($target_class." not found.", 404);
        }

        if ($w && !$obj->canWrite($this->user())) {
            $msg = "Permission denied.";
            throw new Exception($msg, 401);
        }

        if (!$obj->canRead($this->user())) {
            throw new Exception("Unauthorised.", 401);
        }

        return $obj;
    }

    /**
     * Ensure that current user is member of staff.
     *
     * @return bool
     * @since  1.0
     */
    protected function ensureIsStaff()
    {
        if (!$this->session()->isAuth() || $this->user()->groupId() > 2) {
            throw new Exception("Unauthorised.", 401);
        }
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

            $request_param = $this->app()->request()->param($param->getName());
            if (is_null($request_param)){
	            $request_param = $this->app()->request()->param(
	                $param->getName(),
	                $default_value
	            );
            }

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
