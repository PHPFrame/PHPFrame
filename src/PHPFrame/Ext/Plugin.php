<?php
/**
 * PHPFrame/Ext/Plugin.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Ext
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * All plugins handled by the {@link PHPFrame_PluginHandler} will have to 
 * descend from this abstract class.
 * 
 * Methods in this class are the hooks that are called during the application 
 * dispatch process ({@link PHPFrame_Application::dispatch()}) and allow 
 * plugins to modify the state of any of the objects contained in an app 
 * the request ({@link PHPFrame_Request}), reponse ({@link PHPFrame_Response}) 
 * and so on.
 * 
 * All methods in this class are declared as concreto methods that don't do 
 * anything. This is so that implementing classed are not required to implement 
 * any of them. It is up to each plugin to choose which methods to 
 * implement/override.
 * 
 * @category PHPFrame
 * @package  Ext
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since    1.0
 */
abstract class PHPFrame_Plugin
{
	/**
	 * This method is called BEFORE THE REQUEST IS ROUTED to a specific 
	 * controller and action.
	 * 
	 * @param PHPFrame_Application $app Reference to application object.
	 * 
	 * @return void
	 * @since  1.0
	 */
    public function routeStartup(PHPFrame_Application $app) {}
    
    /**
     * This method is called AFTER THE REQUEST IS ROUTED to a specific 
     * controller and action.
     * 
     * @param PHPFrame_Application $app Reference to application object.
     * 
     * @return void
     * @since  1.0
     */
    public function routeShutdown(PHPFrame_Application $app) {}
    
    /**
     * This method is called BEFORE THE DISPATCH LOOP is started. So it will 
     * only run once, regardless of whow many iterations through the dispatch 
     * loop.
     * 
     * @param PHPFrame_Application $app Reference to application object.
     * 
     * @return void
     * @since  1.0
     */
    public function preDispatch(PHPFrame_Application $app) {}
    
    /**
     * This method is called AFTER THE DISPATCH LOOP has finished iterating.  
     * It will only run once, regardless of whow many iterations through the 
     * dispatch loop.
     * 
     * @param PHPFrame_Application $app Reference to application object.
     * 
     * @return void
     * @since  1.0
     */
    public function postDispatch(PHPFrame_Application $app) {}
    
    /**
     * This method is called AT THE BEGINNING OF EVERY ITERATION OF THE 
     * DISPATCH LOOP. It will run once for every iteration of the loop.
     * 
     * @param PHPFrame_Application $app Reference to application object.
     * 
     * @return void
     * @since  1.0
     */
    public function dispatchLoopStartup(PHPFrame_Application $app) {}
    
    /**
     * This method is called AT THE END OF EVERY ITERATION OF THE DISPATCH
     * LOOP. It will run once for every iteration of the loop.
     * 
     * @param PHPFrame_Application $app Reference to application object.
     * 
     * @return void
     * @since  1.0
     */
    public function dispatchLoopShutdown(PHPFrame_Application $app) {}
}
