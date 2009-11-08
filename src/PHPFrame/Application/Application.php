<?php
class PHPFrame_Application
{
    private $_install_dir, $_config_dir, $_var_dir, $_tmp_dir;
    private $_config;
    private $_registry;
    
    public function __construct(array $options)
    {
        if (!isset($options["install_dir"])) {
            $msg  = "Otions array passed to ".get_class($this)."::";
            $msg .= __FUNCTION__."() must contain 'install_dir' key.";
            throw new InvalidArgumentException($msg);
        }
        
        if (!is_string($options["install_dir"])) {
            $msg  = "'install_dir' option passed to ".get_class($this);
            $msg .= " must be of type string and value passed of type '";
            $msg .= gettype($options["install_dir"])."'.";
            throw new InvalidArgumentException($msg);
        }
        
        if (
            !is_dir($options["install_dir"]) 
            || 
            !is_readable($options["install_dir"])
        ) {
            $msg = "Could not read directory ".$options["install_dir"];
            throw new RuntimeException($msg);
        }
        
        $this->_install_dir = $options["install_dir"];
        
        $option_keys = array(
            "config_dir" => "etc", 
            "var_dir"    => "var", 
            "tmp_dir"    => "tmp"
        );
        
        foreach ($option_keys as $key=>$value) {
            $prop_name = "_".$key;
            if (isset($options[$key]) && !is_null($options[$key])) {
                $this->$prop_name = $options[$key];
            } else {
                $this->$prop_name = $this->_install_dir.DS.$value;
            }
            
            if (
                (!is_dir($this->$prop_name) && !mkdir($this->$prop_name))
                ||
                !is_writable($this->$prop_name)
            ) {
                $msg = "Directory ".$this->$prop_name." is not writable.";
                throw new RuntimeException($msg);
            }
        }
        
        spl_autoload_register(array($this, "__autoload"));
        
        // Throw exception if config file doesn't exist
        $config_file = $this->_config_dir.DS."phpframe.ini";
        if (!is_file($config_file)) {
            $msg = "Config file ".$config_file." not found.";
            throw new RuntimeException($msg);
        }
        
        // Acquire config object and cache it
        $this->setConfig(new PHPFrame_Config($config_file));
        
        // Set profiler milestone
        $profiler = self::getProfiler();
        if ($profiler instanceof PHPFrame_Profiler) {
            $profiler->addMilestone();
        }
    }
    
    public static function __autoload($class_name)
    {
        echo $class_name;
        exit;
        // Load custom libraries (if we are in an app)
        if (defined("PHPFRAME_CONFIG_DIR")) {
            $libraries = PHPFrame::AppRegistry()->getLibraries();
            
            foreach ($libraries as $lib) {
                if ($lib->getName() == $class_name) {
                    $file_path  = PHPFRAME_INSTALL_DIR.DS."lib".DS;
                    $file_path .= $lib->getPath();
                    
                    // require the file if it exists
                    if (is_file($file_path)) {
                        @include $file_path;
                        return;
                    }
                }
            }
        }
    }
    
    public function getConfig()
    {
        return $this->_config;
    }
    
    public function getLog()
    {
        if ($this->getConfig()->get("debug.log_level") <= 0) {
            return;
        }
        
        if (is_null($this->getRegistry()->get("log"))) {
            $log = new PHPFrame_TextLogger($this->_tmp_dir.DS."app.log");
            $this->setLog($log);
        }
        
        return $this->getRegistry()->get("log");
    }
    
    public function getInformer()
    {
        if ($this->getConfig()->get("debug.informer_level") <= 0) {
            return;
        }
        
        if (is_null($this->getRegistry()->get("informer"))) {
            // Create informer
            $recipients = $this->getConfig()->get("debug.informer_recipients");
            $recipients = explode(",", $recipients);
            $mailer     = $this->getMailer();
            
            $this->setInformer(new PHPFrame_Informer($mailer, $recipients));
        }
        
        return $this->getRegistry()->get("informer");
    }
    
    public function getProfiler()
    {
        if ($this->getConfig()->get("debug.profiler_enable") != 1) {
            return;
        }
        
        if (is_null($this->getRegistry()->get("profiler"))) {
            $this->setProfiler(new PHPFrame_Profiler());
        }
        
        return $this->getRegistry()->get("profiler");
    }
    
    public function getRegistry()
    {
        if (is_null($this->_registry)) {
            $cache_dir = $this->_tmp_dir.DS."cache";
            PHPFrame_Filesystem::ensureWritableDir($cache_dir);
            $cache_file = $cache_dir.DS."app.reg";
            $this->setRegistry(new PHPFrame_FileRegistry($cache_file));
        }
        
        return $this->_registry;
    }
    
    /**
     * Get database object
     * 
     * @param array $options [Optional] An associative array containing the  
     *                       following options: 
     *                         - db.driver (required)
     *                         - db.name (required)
     *                         - db.host
     *                         - db.user
     *                         - db.pass
     *                         - db.mysql_unix_socket
     *                       This parameter is optional. If omitted options
     *                       will be loaded from etc/phpframe.ini.
     * 
     * @access public
     * @return PHPFrame_Database
     * @since  1.0
     */
    public function getDB(array $options=null)
    {
        if (is_null($options)) {
            $it = new RegexIterator(
                new IteratorIterator($this->getConfig()), 
                '/^db\./', 
                RegexIterator::MATCH, 
                RegexIterator::USE_KEY
            );
            $options = iterator_to_array($it);
        }
        
        if (
           !array_key_exists("db.driver", $options) 
           || !array_key_exists("db.name", $options)
        ) {
            $msg  = "If options array is provided db.driver and db.name  are ";
            $msg .= "required";
            throw new InvalidArgumentException($msg);
        }
        
        $dsn = strtolower($options["db.driver"]);
        if ($dsn == "sqlite") {
            $dsn .= ":";
            if (!preg_match('/^\//', $options["db.name"])) {
                $dsn .= $this->_var_dir.DS;
            }
            $dsn .= $options["db.name"];
        } elseif ($dsn == "mysql") {
            $dsn .= ":dbname=".$options["db.name"];
            if (isset($options["db.host"]) && !empty($options["db.host"])) {
                $dsn .= ";host=".$options["db.host"].";";
            }
            if (
                isset($options["db.mysql_unix_socket"]) 
                && !empty($options["db.mysql_unix_socket"])
            ) {
                $dsn .= ";unix_socket=".$options["db.mysql_unix_socket"];
            } else {
                $dsn .= ";unix_socket=".ini_get('mysql.default_socket');
            }
        } else {
            $msg = "Database driver not supported.";
            throw new Exception($msg);
        }
        
        if (isset($options["db.user"]) && !empty($options["db.user"])) {
            $db_user = $options["db.user"];
        } else {
            $db_user = null;
        }
        
        if (isset($options["db.pass"]) && !empty($options["db.pass"])) {
            $db_pass = $options["db.pass"];
        } else {
            $db_pass = null;
        }
        
        if (isset($options["db.prefix"]) && !empty($options["db.prefix"])) {
            $db_prefix = $options["db.prefix"];
        } else {
            $db_prefix = null;
        }
        
        return PHPFrame_Database::getInstance(
            $dsn, 
            $db_user, 
            $db_pass, 
            $db_prefix
        );
    }
    
    public function getMailer()
    {
        if (is_null($this->getRegistry()->get("mailer"))) {
            $this->setMailer(new PHPFrame_Mailer());
        }
        
        return $this->getRegistry()->get("mailer");
    }
    
    public function getPermissions()
    {
        if (is_null($this->getRegistry()->get("permissions"))) {
        	// Create mapper for ACL objects
        	$mapper = new PHPFrame_Mapper(
	            "PHPFrame_ACL", 
        	    $this->_config_dir, 
	            "acl"
	        );
	        
            $this->setPermissions(new PHPFrame_Permissions($mapper));
        }
        
        return $this->_registry->get("permissions");
    }
    
    public function getLibraries()
    {
        if (is_null($this->getRegistry()->get("libraries"))) {
        	// Create mapper for PHPFrame_LibInfo object
            $mapper = new PHPFrame_Mapper(
                "PHPFrame_LibInfo", 
                $this->_config_dir, 
                "lib"
            );
            
            $this->setLibraries(new PHPFrame_Libraries($mapper));
        }
        
        return $this->_registry->get("libraries");
    }
    
    public function getFeatures()
    {
        if (is_null($this->getRegistry()->get("features"))) {
        	// Create mapper for PHPFrame_Features object
            $mapper = new PHPFrame_Mapper(
                "PHPFrame_Features", 
                $this->_config_dir, 
                "features"
            );
            
            $this->setFeatures(new PHPFrame_Features($mapper));
        }
        
        return $this->_registry->get("features");
    }
    
    public function getPlugins()
    {
    	if (is_null($this->getRegistry()->get("plugins"))) {
    		// Create mapper for PHPFrame_Plugins object
            $mapper = new PHPFrame_Mapper(
                "PHPFrame_Plugins", 
                $this->_config_dir, 
                "plugins"
            );
            
            $this->setPlugins(new PHPFrame_Plugins($mapper));
        }
        
        return $this->_registry->get("plugins");
    }
    
    public function Fire()
    {
        /**
         * Register MVC autoload function
         */
        spl_autoload_register(array("PHPFrame_MVCFactory", "__autoload"));
        
        $frontcontroller = new PHPFrame_FrontController();
        $frontcontroller->run();
    }
    
    public function setConfig(PHPFrame_Config $config)
    {
        $this->_config = $config;
        
        // Set timezone
        date_default_timezone_set($config->get("timezone"));
    }
    
    public function setRegistry(PHPFrame_FileRegistry $file_registry)
    {
        $this->_registry = $file_registry;
    }
    
    public function setLog(PHPFrame_Logger $log)
    {
        $this->getRegistry()->set("log", $log);
        
        // Attach logger observer to exception handler
        PHPFrame_ExceptionHandler::instance()->attach($log);
    }
    
    public function setInformer(PHPFrame_Informer $informer)
    {
        $this->getRegistry()->set("informer", $informer);
        
        // Attach informer to exception handler
        PHPFrame_ExceptionHandler::instance()->attach($informer);
    }
    
    public function setProfiler(PHPFrame_Profiler $profiler)
    {
        $this->getRegistry()->set("profiler", $profiler);
    }
    
    public function setDB(PHPFrame_Database $db)
    {
        $this->getRegistry()->set("db", $db);
    }
    
    public function setMailer(PHPFrame_Mailer $mailer)
    {
        $this->getRegistry()->set("mailer", $mailer);
    }
    
    public function setIMAP(PHPFrame_IMAP $imap)
    {
        $this->getRegistry()->set("imap", $imap);
    }
    
    public function setPermissions(PHPFrame_Permissions $permissions)
    {
    	$this->getRegistry()->set("permissions", $permissions);
    }
    
    public function setFeatures(PHPFrame_Features $features)
    {
        $this->getRegistry()->set("features", $features);
    }
    
    public function setPlugins(PHPFrame_Plugins $plugins)
    {
        $this->getRegistry()->set("plugins", $plugins);
    }
    
    public function setLibraries(PHPFrame_Libraries $libraries)
    {
    	$this->getRegistry()->set("libraries", $libraries);
    }
}
