<?php
class ExtInstaller
{
    private $_types = array("feature", "theme", "lib");
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
    
    public function install($ext_name, $ext_type="feature")
    {
        if (!in_array($ext_type, $this->_types)) {
            $msg = "Extenion type unknown";
            throw new InvalidArgumentException($msg);
        }
        
        // Check if already downloaded latest version
        
        // download package from preferred mirror
        $file_name     = "PHPFrame_".ucfirst($ext_type)."s_";
        $file_name    .= ucfirst($ext_name)."-0.0.1";
        $url           = $this->_preferred_mirror."/";
        $url          .= $ext_type."s/";
        $url          .= $file_name.".tgz";
        $download_tmp  = $this->_install_dir.DS."tmp".DS."download";
        $target        = $download_tmp.DS.$file_name.".tgz";
        
        // Make sure we can write in download directory
        PHPFrame_Filesystem::ensureWritableDir($download_tmp);
        
        echo "Attempting to download ".$url."...\n";
        
        // Create the download listener
        $download = new PHPFrame_DownloadRequestListener();
        $download->setTarget($target);
        
        // Create the http request
        $req = new HTTP_Request($url);
        $req->attach($download);
        @$req->sendRequest(false);
        
        // If response is not OK we throw exception
        if ($req->getResponseCode() != 200) {
            $msg  = "Error downloading package. ";
            $msg .= "Reason: ".$req->getResponseReason();
            throw new RuntimeException($msg);
        }
        
        echo "\nExtracting archive...\n";
        
        // Extract archive in install dir
        $tmp_dir = $download_tmp.DS.$file_name;
        PHPFrame_Filesystem::ensureWritableDir($tmp_dir);
        
        $archive = new Archive_Tar($target, "gz");
        $archive->extract($tmp_dir);
        
        // Validate XML file
        $serialiser = new XML_Unserializer();
        if ($serialiser->unserialize($tmp_dir.DS.$ext_type.".xml", true)) {
            $raw = $serialiser->getUnserializedData();
            if ($raw instanceof PEAR_Error) {
                $msg = "Error reading extension XML definition file";
                throw new RuntimeException($msg);
            }
        }
        var_dump($raw);
        exit;
        
        $class_name = "PHPFrame_Addons_".ucfirst($ext_type)."Info";
        $ext = new $class_name();
        var_dump($ext);
        exit;
        
        // Copy files to destination
//        if (copy()) {
//            $msg = "Could not copy extension files";
//            throw new RuntimeException($msg);
//        }
        
        // Run install script
        
        // Register extension in xml file
    }
    
    public function update($ext_name)
    {
        
    }
    
    public function remove($ext_name)
    {
        
    }
}
