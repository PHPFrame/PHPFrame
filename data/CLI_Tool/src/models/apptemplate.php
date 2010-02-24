<?php
/**
 * data/CLITool/src/models/apptemplate.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   PHPFrame_CLITool
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * Application Template manager class.
 * 
 * @category PHPFrame
 * @package  PHPFrame_CLITool
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class AppTemplate
{
    private $_install_dir = null;
    private $_preferred_mirror = null;
    private $_preferred_state = null;
    
    /**
     * Constructor.
     * 
     * @param string $install_dir      Absolute path to installation directory.
     * @param string $preferred_mirror [Optional] Default value is 
     *                                 'http://dist.phpframe.org'.
     * 
     * @return void
     * @since  1.0
     */
    public function __construct(
        $install_dir, 
        $preferred_mirror="http://dist.phpframe.org"
    ) {
        $this->_install_dir       = trim((string) $install_dir);
        $this->_preferred_mirror  = trim((string) $preferred_mirror);
        $this->_preferred_mirror .= "/app_templates";
    }
    
    /**
     * Install a new application using app template in current install dir.
     * 
     * @param string $app_name            The name for the new app.
     * @param string $template            [Optional] Default value is 'basic'.
     * @param bool   $allow_non_empty_dir [Optional]
     * 
     * @return void
     * @throws RuntimeException
     * @since  1.0
     */
    public function install(
        $app_name, 
        $template="basic", 
        $allow_non_empty_dir=false
    ) {
        // before anything else we check that the directory is writable
        PHPFrame_Filesystem::ensureWritableDir($this->_install_dir);
        
        // Fetch package from dist server
        $this->_fetchSource($template, $allow_non_empty_dir);
        
        // Create configuration xml files based on distro templates
        $this->_createConfig(array("app_name"=>(string) $app_name));
        
        //Create writable tmp and var folders for app
        PHPFrame_Filesystem::ensureWritableDir($this->_install_dir.DS."tmp");
        PHPFrame_Filesystem::ensureWritableDir($this->_install_dir.DS."var");
    }
    
    /**
     * Remove application from current install dir.
     * 
     * @return void
     * @throws RuntimeException
     * @since  1.0
     */
    public function remove()
    {
        PHPFrame_Filesystem::rm($this->_install_dir, true);
    }
    
    /**
     * Fetch app template source from remote server and extract in install dir.
     * 
     * @param string $template            [Optional]
     * @param bool   $allow_non_empty_dir [Optional]
     *  
     * @return void
     * @throws RuntimeException
     * @since  1.0
     */
    private function _fetchSource($template="basic", $allow_non_empty_dir=false)
    {
        // check that the directory is empty
        $is_empty_dir = PHPFrame_Filesystem::isEmptyDir($this->_install_dir);
        if (!$allow_non_empty_dir && !$is_empty_dir) {
            $msg = "Target directory is not empty. ";
            $msg .= "Use \"phpframe app create app_name=MyApp ";
            $msg .= "allow_non_empty_dir=true\" to force install";
            throw new RuntimeException($msg);
        }
        
        //TODO: before we download we should check whether local files exists
        // for current version
        
        // download package from preferred mirror
        $file_name     = "PHPFrame_AppTemplate-1.0.tgz";
        $url           = $this->_preferred_mirror."/".$file_name;
        $download_tmp  = PHPFrame_Filesystem::getSystemTempDir();
        $download_tmp .= DS."PHPFrame".DS."download";
        
        // Make sure we can write in download directory
        PHPFrame_Filesystem::ensureWritableDir($download_tmp);
        
        // Create the http request
        $request  = new PHPFrame_HTTPRequest($url);
        $response = $request->download($download_tmp, $file_name);
        
        echo "\n";
        
        // If response is not OK we throw exception
        if ($response->getStatus() != 200) {
            $msg  = "Error downloading package. ";
            $msg .= "Reason: ".$response->getReasonPhrase();
            throw new RuntimeException($msg);
        }
        
        // Extract archive in install dir
        $archive = new Archive_Tar($download_tmp.DS.$file_name, "gz");
        $archive->extract($this->_install_dir);
    }
    
    /**
     * Create config file.
     * 
     * @param array $array Array containing config data.
     * 
     * @return void
     * @throws InvalidArgumentException
     * @since  1.0
     */
    private function _createConfig($array)
    {
        if (!is_array($array)) {
            $msg = get_class($this)."::_createConfig()";
            $msg .= " expected an array as only argument.";
            throw new InvalidArgumentException($msg);
        }
        
        // Instantiate new config object
        $dist_config_ini  = PHPFrame::dataDir();
        $dist_config_ini .= DS."CLI_Tool".DS."etc".DS."phpframe.ini";
        $config = new PHPFrame_Config($dist_config_ini);
        
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
}
