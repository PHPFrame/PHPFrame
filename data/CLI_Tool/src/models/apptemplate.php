<?php
class AppTemplate
{
    private $_install_dir = null;
    private $_preferred_mirror = null;
    private $_preferred_state = null;
    
    public function __construct($install_dir)
    {
        $this->_install_dir = (string) trim($install_dir);
        
        $config = PHPFrame::Config();
        $this->_preferred_mirror  = $config->get("sources.preferred_mirror");
        $this->_preferred_mirror .= "/app_templates";
        $this->_preferred_state   = $config->get("sources.preferred_state");
    }
    
    public function install(
        array $config, 
        $template=null, 
        $allow_non_empty_dir=false
    )
    {
        if (!isset($config["app_name"]) || empty($config["app_name"])) {
            $msg = "App name is required";
            throw new InvalidArgumentException($msg);
        }
        
        // before anything else we check that the directory is writable
        PHPFrame_Filesystem::ensureWritableDir($this->_install_dir);
        
        // Fetch package from dist server
        $this->_fetchSource($template, $allow_non_empty_dir);
        
        // Create configuration xml files based on distro templates
        $this->_createConfig($config);
        
        // Create dummy controller
        $this->_createDummyController();
        
        //Create writable tmp and var folders for app
        PHPFrame_Filesystem::ensureWritableDir($this->_install_dir.DS."tmp");
        PHPFrame_Filesystem::ensureWritableDir($this->_install_dir.DS."var");
    }
    
    public function update()
    {
        //...
    }
    
    public function remove()
    {
        $cmd = "rm -rf ".$this->_install_dir.DS."*";
        $msg = "Removing app. Using command \"".$cmd."\"...";
        PHPFrame::Session()->getSysevents()
                           ->append($msg, PHPFrame_Subject::EVENT_TYPE_INFO);
        
        $exec = new PHPFrame_Exec($cmd);
        
        if ($exec->getReturnVar() > 0) {
            $msg = "Failed to remove app.";
            throw new RuntimeException($msg);
        }
    }
    
    private function _fetchSource($template="Full", $allow_non_empty_dir=false)
    {
        // check that the directory is empty
        $is_empty_dir = PHPFrame_Filesystem::isEmptyDir($this->_install_dir);
        if (!$allow_non_empty_dir && !$is_empty_dir) {
            $msg = "Target directory is not empty. ";
            $msg .= "Use \"phpframe app new_app app_name=MyApp ";
            $msg .= "allow_non_empty_dir=true\" to force install";
            throw new RuntimeException($msg);
        }
        
        //TODO: before we download we should check whether local files exists
        // for current version
        
        // download package from preferred mirror
        $file_name     = "PHPFrame_AppTemplate-Full-0.0.1.tgz";
        $url           = $this->_preferred_mirror."/".$file_name;
        $download_tmp  = PHPFrame_Filesystem::getSystemTempDir();
        $download_tmp .= DS."PHPFrame".DS."download";
        
        // Make sure we can write in download directory
        PHPFrame_Filesystem::ensureWritableDir($download_tmp);
        
        $msg = "Attempting to download ".$url."...";
        PHPFrame::Session()->getSysevents()
                           ->append($msg, PHPFrame_Subject::EVENT_TYPE_INFO);
        
        // Create the http request
        $request  = new PHPFrame_HTTPRequest($url);
        $response = $request->download($download_tmp, $file_name);
        
        // If response is not OK we throw exception
        if ($response->getStatus() != 200) {
            $msg  = "Error downloading package. ";
            $msg .= "Reason: ".$response->getReasonPhrase();
            throw new RuntimeException($msg);
        }
        
        $msg = "Extracting archive...";
        PHPFrame::Session()->getSysevents()
                           ->append($msg, PHPFrame_Subject::EVENT_TYPE_INFO);
        
        // Extract archive in install dir
        $archive = new Archive_Tar($download_tmp.DS.$file_name, "gz");
        $archive->extract($this->_install_dir);
    }
    
    private function _createConfig($array)
    {
        if (!is_array($array)) {
            $msg = get_class($this)."::_createConfig()";
            $msg .= " expected an array as only argument.";
            throw new InvalidArgumentException($msg);
        }
        
        $msg = "Creating configuration file...";
        PHPFrame::Session()->getSysevents()
                           ->append($msg, PHPFrame_Subject::EVENT_TYPE_INFO);
        
        // Instanciate new config object
        $dist_config_ini = PEAR_Config::singleton()->get("data_dir");
        $dist_config_ini .= DS."PHPFrame".DS."etc".DS."phpframe.ini";
        $config = PHPFrame_Config::instance($dist_config_ini);
        
        // Bind to array
        $config->bind($array);
        
        // Create random secret string
        $config->set("secret", md5(uniqid()));
        
        // Write to filesystem
        $config_path = $this->_install_dir.DS."etc";
        
        // Make sure we can write in etc directory
        PHPFrame_Filesystem::ensureWritableDir($config_path);
        
        $config_file_name = $config_path.DS."phpframe.ini";
        $config->store($config_file_name);
    }
    
    private function _createDummyController()
    {
        $source = PEAR_Config::singleton()->get("data_dir");
        $source .= DS."PHPFrame".DS."DummyController.php";
        $target = $this->_install_dir.DS."src".DS."controllers".DS."dummy.php";
        
        $msg = "Creating dummy controller...";
        PHPFrame::Session()->getSysevents()
                           ->append($msg, PHPFrame_Subject::EVENT_TYPE_INFO);
        
        if (!copy($source, $target)) {
            $msg = "Failed to create dummy controller.";
            throw new RuntimeException($msg);   
        }
    }
}
