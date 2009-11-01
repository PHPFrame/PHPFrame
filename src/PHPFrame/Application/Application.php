<?php
class PHPFrame_Application
{
	private $_install_dir, $_config_dir, $_var_dir, $_tmp_dir;
	private $_config;
	private $_log, $_informer, $_profiler;
	
	
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
		
		if (!is_dir($options["install_dir"]) || !is_readable($options["install_dir"])) {
		    $msg = "Could not read directory ".$options["install_dir"];
		    throw new RuntimeException($msg);
		}
		
		$this->_install_dir = $options["install_dir"];
		
		$option_keys = array("config_dir"=>"etc", "var_dir"=>"var", "tmp_dir"=>"tmp");
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
		print_r($this);
		exit;
		
		self::Profiler()->addMilestone();
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
                    $file_path = PHPFRAME_INSTALL_DIR.DS."lib".DS.$lib->getPath();
                    
                    // require the file if it exists
                    if (is_file($file_path)) {
                        @include $file_path;
                        return;
                    }
                }
            }
        }
	}
	
	public function Config()
	{
		
	}
	
	public function Log()
	{
		
	}
	
	public function Informer()
	{
		
	}
	
	public function Profiler()
	{
	    if (!PHPFrame::Config()->get("debug.profiler_enable")) {
            return;
        }
	}
	
	public function Registry()
	{
		
	}
	
	public function DB()
	{
		
	}
	
	public function Fire()
	{
		
	}
	
	public function setConfig(PHPFrame_Config $config)
	{
        $this->_config = $config;
        
        // Attach logger observer to exception handler
        if ($config->get("debug.log_level") > 0) {
        	$this->_log = new PHPFrame_TextLogger($this->_tmp_dir.DS."app.log");
            PHPFrame_ExceptionHandler::instance()->attach($this->_log);
        }
		
		// Set app profiler
		if ($config->get("debug.profiler_enable") == 1) {
		    $this->setProfiler(new PHPFrame_Profiler());
		}
        
        // Attach informer observer to excpetion handler if informer is enabled
        if ($config->get("debug.informer_level") > 0) {
            // Create informer
            $recipients = explode(",", $config->get("debug.informer_recipients"));
            $mailer     = new PHPFrame_Mailer();
            $informer   = new PHPFrame_Informer($mailer, $recipients);
            
            // Attach informer to exception handler
            PHPFrame_ExceptionHandler::instance()->attach($informer);
        }
        
        // Set timezone
        date_default_timezone_set($config->get("timezone"));
	}
	
	public function setLog(PHPFrame_Log $log)
	{
		$this->_log = $log;
	}
	
	public function setInformer(PHPFrame_Informer $informer)
	{
		$this->_informer = $informer;
	}
	
	public function setProfiler(PHPFrame_Profiler $profiler)
	{
		$this->_profiler = $profiler;
	}
}
