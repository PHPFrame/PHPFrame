<?php
class DeployModel extends PHPFrame_MVC_Model
{
    private $_install_dir=null;
    private $_sources_config=null;
    
    public function __construct($install_dir)
    {
        $this->_install_dir = (string) trim($install_dir);
        
        $xml_path = $this->_install_dir.DS."etc".DS."sources.xml";
        
        if (!is_file($xml_path)) {
            $xml_path = PEAR_Config::singleton()->get("data_dir");
            $xml_path .= DS."PHPFrame".DS."etc".DS."sources.xml";
        }
        
        $this->_sources_config = PHPFrame_Config::instance($xml_path);
    }
    
    public function install($config=array())
    {
        if (!isset($config["APPNAME"]) || empty($config["APPNAME"])) {
            $msg = "App name is required";
            throw new PHPFrame_Exception($msg);
        }
        
        // Fetch source from repo
        $this->_fetchSource();
        
        // Create configuration xml files based on distro templates
        $this->_createConfig($config);
        
        // Create dummy controller
        $this->_createDummyController();
    }
    
    public function update()
    {
        $source = $this->_sources_config->get("PHPFrame_AppTemplate").DS;
        $tmp_target = $this->_install_dir.DS."var".DS."tmp.install".DS."AppTemplate".DS;
        
        $cmd = "svn export --force ".$source." ".$tmp_target;
        
        $msg = "Exporting latest version of AppTemplate...\n";
        $msg .= "Using command \"".$cmd."\"...";
		PHPFrame_Debug_Logger::write($msg);
		
		$exec = new PHPFrame_Utils_Exec($cmd);
		
        // Log output to file
		PHPFrame_Debug_Logger::write($exec->getOutput());
		
		if ($exec->getReturnVar() > 0) {
			$msg = "Failed to export latest version of AppTemplate.";
            throw new PHPFrame_Exception($msg);
		}
        
        $cmd = "cp -r ".$tmp_target.DS." ".$this->_install_dir.DS;
		
        $msg = "Overwriting files...\n";
        $msg .= "Using command \"".$cmd."\"...";
		PHPFrame_Debug_Logger::write($msg);
		
		$exec = new PHPFrame_Utils_Exec($cmd);
		
		// Log output to file
		PHPFrame_Debug_Logger::write($exec->getOutput());
		
		if ($exec->getReturnVar() > 0) {
			$msg = "Failed to overwrite files.";
            throw new PHPFrame_Exception($msg);
		}
		
        $cmd = "rm -rf ".$tmp_target;
		
        $msg = "Deleting temporary files...\n";
        $msg .= "Using command \"".$cmd."\"...";
		PHPFrame_Debug_Logger::write($msg);
		
		$exec = new PHPFrame_Utils_Exec($cmd);
		
		// Log output to file
		PHPFrame_Debug_Logger::write($exec->getOutput());
		
		if ($exec->getReturnVar() > 0) {
			$msg = "Failed to remove temporary files.";
            throw new PHPFrame_Exception($msg);
		}
    }
    
    public function remove()
    {
        $cmd = "rm -rf ".$this->_install_dir.DS."*";
		
        $msg = "Removing app...\n";
        $msg .= "Using command \"".$cmd."\"...";
		PHPFrame_Debug_Logger::write($msg);
		
		$exec = new PHPFrame_Utils_Exec($cmd);
		
		// Log output to file
		PHPFrame_Debug_Logger::write($exec->getOutput());
		
		if ($exec->getReturnVar() > 0) {
			$msg = "Failed to remove app.";
            throw new PHPFrame_Exception($msg);
		}
    }
    
    private function _fetchSource()
    {
        // before anything else we check that the directory is writable
        PHPFrame_Utils_Filesystem::ensureWritableDir($this->_install_dir);
        
        // check that the directory is empty
        if (!PHPFrame_Utils_Filesystem::isEmptyDir($this->_install_dir)) {
            $msg = "Target directory is not empty.";
            throw new PHPFrame_Exception($msg);
        }
        
        $source = $this->_sources_config->get("PHPFrame_AppTemplate").DS;
        $cmd = "svn export --force ".$source." ".$this->_install_dir.DS;
		
        $msg = "Fetching PHPFrame_AppTemplate source from repository...\n";
        $msg .= "Using command \"".$cmd."\"...";
		PHPFrame_Debug_Logger::write($msg);
		
		$exec = new PHPFrame_Utils_Exec($cmd);
		
		// Log output to file
		PHPFrame_Debug_Logger::write($exec->getOutput());
		
		if ($exec->getReturnVar() > 0) {
			$msg = "Failed to checkout source from repository.";
            throw new PHPFrame_Exception($msg);
		}
    }
    
    private function _createConfig($array)
    {
        if (!is_array($array)) {
            $msg = get_class($this)."::_createConfig()";
            $msg .= " expected an array as only argument.";
            throw new PHPFrame_Exception($msg);
        }
        
		PHPFrame_Debug_Logger::write("Creating configuration file...");
        
        // Instanciate new config object
        $dist_config_xml = PEAR_Config::singleton()->get("data_dir");
        $dist_config_xml .= DS."PHPFrame".DS."etc".DS."config.xml";
        $config = PHPFrame_Config::instance($dist_config_xml);
        
        // Bind to array
        $config->bind($array);
        
        // Write to filesystem
        $config_file = $this->_install_dir.DS;
        $config_file .= "etc".DS."config.xml";
        PHPFrame_Utils_Filesystem::write($config_file, $config->toXML());
        
        // Copy other default XML files
        $files = array("acl.xml", "groups.xml", "lib.xml", "plugins.xml", "sources.xml");
        foreach ($files as $file) {
            $source = PEAR_Config::singleton()->get("data_dir");
            $source .= DS."PHPFrame".DS."etc".DS.$file;
            $target = $this->_install_dir.DS."etc".DS.$file;
            $cmd = "cp ".$source." ".$target;
            
            $exec = new PHPFrame_Utils_Exec($cmd);
            
            // Log output to file
    		PHPFrame_Debug_Logger::write($exec->getOutput());
    		
    		if ($exec->getReturnVar() > 0) {
    			$msg = "Failed to copy ".$file.".";
                throw new PHPFrame_Exception($msg);
    		}
        }
        
    }
    
    private function _createDummyController()
    {
        $source = PEAR_Config::singleton()->get("data_dir");
        $source .= DS."PHPFrame".DS."DummyController.php";
        $target = $this->_install_dir.DS."src".DS."controllers".DS."dummy.php";
        $cmd = "cp ".$source." ".$target;
        
        $exec = new PHPFrame_Utils_Exec($cmd);
        
        // Log output to file
		PHPFrame_Debug_Logger::write($exec->getOutput());
		
		if ($exec->getReturnVar() > 0) {
			$msg = "Failed to create dummy controller.";
            throw new PHPFrame_Exception($msg);
		}
    }
}