<?php
class ExtInstaller
{
    private $_install_dir = null;
    private $_preferred_mirror = null;
    private $_preferred_state = null;
    
    public function __construct($install_dir)
    {
        $this->_install_dir = trim((string) $install_dir);
        
        $config = PHPFrame::Config();
        $this->_preferred_mirror = $config->get("sources.preferred_mirror");
        $this->_preferred_state  = $config->get("sources.preferred_state");
    }
    
    public function install($package)
    {
        $package = trim($package);
        
        // If package was specified by URL we try to download it
        if (preg_match('/^http:\/\//', $package)) {
            $http_request = new PHPFrame_HTTPRequest($url);
            print_r($http_request->download($this->_install_dir.DS."tmp".DS."download"));
            exit;
        }
        
        // If at this point package is archive we extract it
        if (preg_match('/\.(tgz|tar.bz2|zip)$/', $package)) {
            if (!is_file($package)) {
                $msg = "Package file '".$package."' not found.";
                throw new UnexpectedValueException($msg);
            }
            
            // Extract the file
            $msg  = "Package was passed as archive. This feature has not been ";
            $msg .= "implemented yet...";
            throw new LogicException($msg);
        }
        
        // Now we should have package XML file as package
        if (preg_match('/^(.*)\.xml$/', $package, $matches)) {
            if (!is_file($package)) {
                $msg = "Package file '".$package."' not found.";
                throw new UnexpectedValueException($msg);
            }
            
            $array       = explode(DS, $matches[1]);
            $ext_type    = str_replace(".xml", "", array_pop($array));
            $package_dir = implode(DS, $array);
            $info_class  = "PHPFrame_".ucfirst($ext_type)."Info";
        } else {
            $msg  = "Package XML file not found.";
            throw new UnexpectedValueException($msg);
//            $channel = $this->_config->get("sources.preferred_mirror");
//            $package = $channel."/".$package.".tgz";
        }
        
        $mapper = new PHPFrame_Mapper(
            $info_class, 
            $ext_type, 
            PHPFrame_Mapper::STORAGE_XML, 
            false, 
            $package_dir
        );
        
        $ext_info = $mapper->find()->getElement(0);
        
        // Copy files to destination
        foreach ($ext_info->getContents() as $contents) {
            foreach ($contents as $file) {
                $origin = $package_dir.DS.$file["source"];
                $dest   = $this->_install_dir.DS.$file["target"];
                
                if (is_file($dest)) {
                    $msg = "File ".$dest." already exists.";
                    throw new RuntimeException($msg);
                }
                
                PHPFrame_Filesystem::cp($origin, $dest, false);
            }
        }
        
        // Run install script
        $install_script = $package_dir.DS."data".DS."install.php";
        if (is_file($install_script)) {
            require $install_script;
            $install_class  = $ext_info->getName()."Install";
            $reflection_obj = new ReflectionClass($install_class);
            
            if ($reflection_obj->isInstantiable()) {
                $config_file = $this->_install_dir.DS."etc".DS."phpframe.ini";
                $config      = PHPFrame_Config::instance($config_file);
                $dsn_class   = "PHPFrame_".$config->get("db.driver")."DSN";
                $dsn_options = array(
                    "db_host" => $config->get("db.host"),
                    "db_name" => $config->get("db.name")
                );
                $dsn = new $dsn_class($dsn_options);
                $db  = PHPFrame_Database::getInstance(
                    $dsn, 
                    $config->get("db.user"), 
                    $config->get("db.pass")
                );
                $args = array(
                    "install_dir" => $this->_install_dir,
                    "config"      => $config,
                    "db"          => $db
                );
                $install_obj = $reflection_obj->newInstanceArgs($args);
                
                if ($reflection_obj->hasMethod("run")) {
                    $install_obj->run();
                }
            }
        }
        
        // Register extension in xml file
        $mapper = new PHPFrame_Mapper(
            $info_class, 
            $ext_type."s", 
            PHPFrame_Mapper::STORAGE_XML, 
            false, 
            $this->_install_dir.DS."etc"
        );
        
        $mapper->insert($ext_info);
    }
    
    public function update($ext_name)
    {
        
    }
    
    public function remove($ext_name)
    {
        echo $ext_name;
    }
    
    private function checkDependencies()
    {
        
    }
}
