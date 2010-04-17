<?php
/**
 * PHPFrame/MVC/MVCFactory.php
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
 * This class provides a number of "factory" methods used to acquire controllers,
 * models and views.
 *
 * @category PHPFrame
 * @package  MVC
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @see      PHPFrame_ActionController, PHPFrame_View
 * @since    1.0
 */
class PHPFrame_MVCFactory
{
    /**
     * Reference to application object.
     *
     * @var PHPFrame_Application
     */
    private $_app;

    /**
     * Constructor
     *
     * @param PHPFrame_Application $app Reference to application object.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Application $app)
    {
        $this->_app = $app;
    }

    /**
     * Get a named action controller object
     *
     * @param string $controller_name The controller name.
     *
     * @return PHPFrame_ActionController
     * @since  1.0
     */
    public function getActionController($controller_name)
    {
        // Create reflection object for named controller
        $controller_class = ucfirst($controller_name)."Controller";

        // Prepend userland class suffix if needed
        $class_prefix = $this->_app->classPrefix();
        if (!empty($class_prefix)) {
            $controller_class = $class_prefix.$controller_class;
        }

        // Get reflection object to inspect class before instantiating it
        try {
            $reflection_obj = new ReflectionClass($controller_class);
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage(), 404);
        }

        if (!$reflection_obj->isSubclassOf("PHPFrame_ActionController")) {
            $msg  = "Action Controller not supported. ".$controller_class;
            $msg .= " does NOT extend PHPFrame_ActionController.";
            throw new LogicException($msg);
        }

        return $reflection_obj->newInstance($this->_app);
    }

    /**
     * Get a named view.
     *
     * @param string $name The name of the view to get.
     * @param array  $data Data to assign to the view.
     *
     * @return PHPFrame_View
     * @since  1.0
     */
    public function view($name, array $data=null)
    {
        return new PHPFrame_View($name, $data);
    }

    /**
     * Get a named view helper.
     *
     * @param string $name The name of the view to get.
     *
     * @return PHPFrame_View
     * @since  1.0
     */
    public function getViewHelper($name)
    {
        $helper_class = ucfirst($name)."Helper";

        // Prepend userland class suffix if needed
        $class_prefix = $this->_app->classPrefix();
        if (!empty($class_prefix)) {
            $helper_class = $class_prefix.$helper_class;
        }

        // Get reflection object to inspect class before instantiating it
        $reflection_obj = new ReflectionClass($helper_class);

        if (!$reflection_obj->isSubclassOf("PHPFrame_ViewHelper")) {
            $msg  = "View Helper not supported. ".$controller_class;
            $msg .= " does NOT extend PHPFrame_ViewHelper.";
            throw new LogicException($msg);
        }

        return $reflection_obj->newInstance($this->_app);
    }
}
