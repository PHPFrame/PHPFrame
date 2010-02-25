<?php
class aa extends PHPFrame_Plugin
{
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
