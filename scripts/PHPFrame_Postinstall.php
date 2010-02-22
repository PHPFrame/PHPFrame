#!/usr/bin/env php
<?php
/**
 * scripts/postinstall.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Postinstall
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * This class is responsible for post install tasks
 * 
 * @category PHPFrame
 * @package  Postinstall
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_Postinstall_postinstall
{
    /**
     * Path to PEAR install directory
     * 
     * @var string
     */
    private $_install_dir;
    /**
     * Path to PHPFrame data directory
     * 
     * @var string
     */
    private $_data_dir;
    
    /**
     * Constructor
     * 
     * @return void
     * @since  1.0
     */
    public function __construct() {}
    
    /**
     * Initialise
     * 
     * @param PEAR_Config         $config               The current config used  
     *                                                  for installation.
     * @param PEAR_PackageFile_v2 $self                 The package.xml contents 
     *                                                  as abstracted by this 
     *                                                  object.
     * @param string|null         $lastInstalledVersion The last version of this  
     *                                                  package that was 
     *                                                  installed. This is a 
     *                                                  very important parameter 
     *                                                  as it is the only way to 
     *                                                  determine whether a 
     *                                                  package is being 
     *                                                  installed from scratch, 
     *                                                  or upgraded from a 
     *                                                  previous version. Using 
     *                                                  this parameter, it is 
     *                                                  possible to determine 
     *                                                  what incremental changes 
     *                                                  need to be performed,
     *                                                  if any.
     * 
     * @return bool Returns TRUE on success or FALSE on failure
     * @since  1.0
     */
    public function init(
        PEAR_Config $config, 
        PEAR_PackageFile_v2 $self, 
        $lastInstalledVersion=null
    ) {
        // Include PHPFrame framework
        require_once "PHPFrame.php";
        
        if (!class_exists('PHPFrame')) {
            $this->_output("Could not load PHPFrame class.");
            return false;
        }
        
        $this->_install_dir = PEAR_Config::singleton()->get("php_dir");
        $this->_data_dir    = PEAR_Config::singleton()->get("data_dir");
        $this->_data_dir   .= DS."PHPFrame";
        $this->_clitool_dir = $this->_data_dir.DS."CLI_Tool";
        
        $msg = "\nPHPFrame Post Installation Script";
        $msg .= "\n--------------------------------\n";
        $msg .= "\nPHPFrame installation directory: ".$this->_install_dir."\n";
        $msg .= "PHPFrame data directory: ".$this->_data_dir."\n";
        $msg .= "PHPFrame CLI Tool directory: ".$this->_clitool_dir."\n";
        
        echo $msg;
        
        return true;
    }
    
    /**
     * Run
     * 
     * @param array  $infoArray    if $paramGroupId is _undoOnError, then 
     *                             $infoArray will contain a list of 
     *                             successfully completed parameter group 
     *                             sections. This can be used to restore any 
     *                             system changes made by the installation 
     *                             script. Otherwise, $infoArray contains the 
     *                             results of the user input from the most 
     *                             recent <paramgroup> section.
     * @param string $paramGroupId This variable either contains _undoOnError or 
     *                             the contents of the most recent <paramgroup> 
     *                             <id> tag. Note that paramgroup id cannot 
     *                             begin with an underscore (_), and so 
     *                             _undoOnError can only be triggered by the 
     *                             PEAR installer.
     * 
     * @return void
     * @since  1.0
     */
    public function run($infoArray, $paramGroupId)
    {
        if (!isset($infoArray["CONFIRM"]) || $infoArray["CONFIRM"] != "yes") {
            $this->_output("Installation aborted...");
            return false;
        }
        
        if (!$this->_createLogFile($this->_data_dir)
            || !$this->_createCLIToolEtc()
            || !$this->_createCLIToolTmp()
            || !$this->_createCLIToolVar()
        ) {
            $this->_output("Installation failed...");
            return false;
        }
        
        // If we got here installation succeded
        $this->_output("PHPFrame postinstall completed successfully...");
        return true;
    }
    
    private function _createCLIToolEtc()
    {
        $etc_dir = $this->_clitool_dir.DS."etc";
        PHPFrame_Filesystem::ensureWritableDir($etc_dir);
        
        // Create empty config xml files
        $cmd  = "touch ".$etc_dir.DS."acl.xml ";
        $cmd .= $etc_dir.DS."lib.xml ".$etc_dir.DS."features.xml";
        $exec = new PHPFrame_Exec($cmd);
        $this->_output($exec->getOutput());
        if ($exec->getReturnVar() > 0) {
            $msg = "Failed to create empty config xml files in (";
            $msg .= $etc_dir.")...";
            $this->_output($msg);
            return false;
        }
        
        return true;
    }
    
    private function _createCLIToolTmp()
    {
        $tmp_dir = $this->_clitool_dir.DS."tmp";
        PHPFrame_Filesystem::ensureWritableDir($tmp_dir);
        
        if (!$this->_createLogFile($tmp_dir)) {
            $this->_output("Error creating log file for CLI tool...");
            $this->_output("Installation failed...");
            return false;
        }
        
        // Make tmp directory for CLI tool world writable
        $cmd = "chmod 777 ".$tmp_dir;
        $exec = new PHPFrame_Exec($cmd);
        $this->_output($exec->getOutput());
        if ($exec->getReturnVar() > 0) {
            $msg = "Failed to make world writable var directory for CLI tool (";
            $msg .= $tmp_dir.")...";
            $this->_output($msg);
            return false;
        }
        
        // Clear app cache if it exists
        $app_reg_cache = $tmp_dir.DS."cache".DS."application.registry";
        if (is_file($app_reg_cache)) {
            $cmd = "rm ".$app_reg_cache;
            $exec = new PHPFrame_Exec($cmd);
            $this->_output($exec->getOutput());
            if ($exec->getReturnVar() > 0) {
                $msg = "Failed to clear cached application registry (";
                $msg .= $app_reg_cache.")...";
                $this->_output($msg);
                return false;
            }
        }
        
        return true;
    }
    
    private function _createCLIToolVar()
    {
        $var_dir = $this->_clitool_dir.DS."var";
        PHPFrame_Filesystem::ensureWritableDir($var_dir);
        
        return true;
    }
    
    /**
     * Create a log file in a given directory
     * 
     * @param string $path
     * 
     * @return bool
     * @since  1.0
     */
    private function _createLogFile($path)
    {
        $log  = $path.DS."log";
        $cmd  = "touch ".$log;
        $exec = new PHPFrame_Exec($cmd);
        $this->_output($exec->getOutput());
        if ($exec->getReturnVar() > 0) {
            $this->_output("Failed to touch new log file (".$log.")...");
            return false;
        }
        
        $cmd  = "chmod 666 ".$log;
        $exec = new PHPFrame_Exec($cmd);
        $this->_output($exec->getOutput());
        if ($exec->getReturnVar() > 0) {
            $this->_output("Failed to make log file writable (".$log.")...");
            return false;
        }
        
        return true;
    }
    
    /**
     * Output a message
     * 
     * @param string $msg
     * @param bool   $trigger_error
     * 
     * @return void
     * @since  1.0
     */
    private function _output($msg, $trigger_error=false)
    {
        // Convert messages in array format to string
        if (is_array($msg)) {
            $msg = implode("\n", $msg);
        }
        
        // Echo message to user
        echo $msg."\n";
        
        // Trigger PHP error is flag passed
        if ($trigger_error) {
            trigger_error($msg);
        }
    }
}
