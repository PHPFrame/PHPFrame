<?php
class PHPFrame_PluginHandler
{
    private static $_instance = null;
    private $_plugins = null;
    private $_events = array(
        "routeStartup", 
        "routeShutdown", 
        "dispatchLoopStartup",
        "dispatchLoopShutdown",
        "preDispatch",
        "postDispatch"
    );
    
    public function __construct()
    {
        $this->_plugins = new SplObjectStorage();
    }
    
    public function registerPlugin(PHPFrame_Plugin $plugin)
    {
        $this->_plugins->attach($plugin);
    }
    
    public function unregisterPlugin(PHPFrame_Plugin $plugin)
    {
        $this->_plugins->detach($plugin);
    }
    
    public function handle($event)
    {
        $event = trim((string) $event);
        if (!in_array($event, $this->_events)) {
            $msg  = "Plugin event not supported. The plugin handler supports ";
            $msg .= "'".implode("', '", $this->_events)."'";
            throw new LogicException($msg);
        }
        
        foreach ($this->_plugins as $plugin) {
            $plugin->$event();
        }
    }
}