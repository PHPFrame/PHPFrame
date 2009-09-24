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
        
        $plugins_path  = PHPFRAME_INSTALL_DIR;
        $plugins_path .= DIRECTORY_SEPARATOR."src";
        $plugins_path .= DIRECTORY_SEPARATOR."plugins";
        set_include_path($plugins_path.PATH_SEPARATOR.get_include_path());
        
        /**
         * Register MVC autoload function
         */
        spl_autoload_register(array("PHPFrame_PluginHandler", "__autoload"));
    }
    
    public static function __autoload($class_name)
    {
        require strtolower(trim($class_name)).".php";
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