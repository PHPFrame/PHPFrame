<?php
/**
 * PHPFrame/Ext/Plugin.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Ext
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * All plugins handled by the {@link PHPFrame_PluginHandler} will have to
 * descend from this abstract class.
 *
 * Methods in this class are the hooks that are called during the application
 * dispatch process ({@link PHPFrame_Application::dispatch()}) and allow
 * plugins to modify the state of any of the objects contained in an app,
 * including the request ({@link PHPFrame_Request}), response
 * ({@link PHPFrame_Response}) and so on.
 *
 * All methods in this class are declared as concrete methods that don't do
 * anything. This is so that implementing classed are not required to implement
 * any of them. It is up to each plugin to choose which methods to
 * implement/override.
 *
 * @category PHPFrame
 * @package  Ext
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
abstract class PHPFrame_Plugin
{
    /**
     * Static reference to application object.
     *
     * @var PHPFrame_Application
     */
    private static $_app;

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
        self::$_app = $app;
    }

    /**
     * Get reference to application object.
     *
     * @return PHPFrame_Application
     * @since  1.0
     */
    protected function app()
    {
        return self::$_app;
    }

    /**
     * This method is called BEFORE THE REQUEST IS ROUTED to a specific
     * controller and action.
     *
     * @return void
     * @since  1.0
     */
    public function routeStartup()
    {
        //...
    }

    /**
     * This method is called AFTER THE REQUEST IS ROUTED to a specific
     * controller and action.
     *
     * @return void
     * @since  1.0
     */
    public function routeShutdown()
    {
        //...
    }

    /**
     * This method is called BEFORE THE DISPATCH LOOP is started. So it will
     * only run once, regardless of whow many iterations through the dispatch
     * loop.
     *
     * @return void
     * @since  1.0
     */
    public function preDispatch()
    {
        //...
    }

    /**
     * This method is called AFTER THE DISPATCH LOOP has finished iterating.
     * It will only run once, regardless of whow many iterations through the
     * dispatch loop.
     *
     * @return void
     * @since  1.0
     */
    public function postDispatch()
    {
        //...
    }

    /**
     * This method is called AT THE BEGINNING OF EVERY ITERATION OF THE
     * DISPATCH LOOP. It will run once for every iteration of the loop.
     *
     * @return void
     * @since  1.0
     */
    public function dispatchLoopStartup()
    {
        //...
    }

    /**
     * This method is called AT THE END OF EVERY ITERATION OF THE DISPATCH
     * LOOP. It will run once for every iteration of the loop.
     *
     * @return void
     * @since  1.0
     */
    public function dispatchLoopShutdown()
    {
        //...
    }

    /**
     * This method is called AFTER THE DISPATCH LOOP AND BEFORE THE THEME IS
     * APPLIED TO THE RESPONSE BODY.
     *
     * @return void
     * @since  1.0
     */
    public function preApplyTheme()
    {
        //...
    }

    /**
     * This method is called AFTER THE THEME IS APPLIED TO THE RESPONSE BODY
     * and it is the last hook to be called before the application ends
     * execution.
     *
     * @return void
     * @since  1.0
     */
    public function postApplyTheme()
    {
        //...
    }
}
