#!/usr/bin/env php
<?php
/**
 * scripts/postinstall.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * This class is responsible for post install tasks
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Postinstall
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class Postinstall_postinstall
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
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct() {}
    
    /**
     * Initialise
     * 
     * @param PEAR_Config         $config               The current configuration used for 
     *                                                  installation.
     * @param PEAR_PackageFile_v2 $self                 The package.xml contents as abstracted 
     *                                                  by this object.
     * @param string|null         $lastInstalledVersion The last version of this package 
     *                                                  that was installed. This is a very 
     *                                                  important parameter, as it is the 
     *                                                  only way to determine whether a 
     *                                                  package is being installed from 
     *                                                  scratch, or upgraded from a previous 
     *                                                  version. Using this parameter, it is 
     *                                                  possible to determine what incremental 
     *                                                  changes, if any, need to be performed.
     * 
     * @access public
     * @return bool Returns TRUE on success or FALSE on failure
     * @since  1.0
     */
    public function init(PEAR_Config $config, PEAR_PackageFile_v2 $self, $lastInstalledVersion=null)
    {
        // Include PHPFrame framework]
        require_once "PHPFrame.php";
        
        if (!class_exists('PHPFrame')) {
			$this->_output("Missing PHPFrame. Please check your PEAR installation.");
			return false;
        }
        
        $this->_install_dir = PEAR_Config::singleton()->get("php_dir");
        $this->_data_dir = PEAR_Config::singleton()->get("data_dir");
        $this->_data_dir .= DS."PHPFrame";
        
		$msg = "\nPHPFrame Post Installation Script";
		$msg .= "\n--------------------------------\n\n";
		$msg .= "\nInstallation directory: ".$this->_install_dir."\n\n";
        
		echo $msg;
		
        return true;
    }
    
    /**
     * Run
     * 
     * @param array  $infoArray    if $paramGroupId is _undoOnError, then $infoArray will 
     *                             contain a list of successfully completed parameter group 
     *                             sections. This can be used to restore any system changes 
     *                             made by the installation script. Otherwise, $infoArray 
     *                             contains the results of the user input from the most 
     *                             recent <paramgroup> section.
     * @param string $paramGroupId This variable either contains _undoOnError or the contents 
     *                             of the most recent <paramgroup>'s <id> tag. Note that 
     *                             paramgroup id cannot begin with an underscore (_), and 
     *                             so _undoOnError can only be triggered by the PEAR installer.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function run($infoArray, $paramGroupId)
    {	
        $cli_tool_log_file = $this->_data_dir;
        if (!$this->_createLogFile($log_file)) {
			$this->_output("Error creating log file...");
			$this->_output("Installation failed...");
			return false;
		}
		
		$cli_tool_log_file = $this->_data_dir.DS."CLI_Tool".DS."var";
        if (!$this->_createCLIToolLogFile($cli_tool_log_file)) {
            $this->_output("Error creating log file for CLI tool...");
            $this->_output("Installation failed...");
            return false;
        }
		
		// If we got here installation succeded
		$this->_output("PHPFrame postinstall completed successfully...");
		return true;
    }
    
    /**
     * Create a log file in a given directory
     * 
     * @param string $path
     * 
     * @access private
     * @return bool
     * @since  1.0
     */
    private function _createLogFile($path)
    {
        PHPFrame_Utils_Filesystem::ensureWritableDir($path);
        
        $log_file = $path.DS."log";
        $cmd = "touch ".$log_file;
        $exec = new PHPFrame_Utils_Exec($cmd);
        $this->_output($exec->getOutput());
        if ($exec->getReturnVar() > 0) {
            $this->_output("Failed to touch new log file (".$log_file.")...");
            return false;
        }
        
        $cmd = "chmod 666 ".$log_file;
        $exec = new PHPFrame_Utils_Exec($cmd);
        $this->_output($exec->getOutput());
        if ($exec->getReturnVar() > 0) {
            $this->_output("Failed to make log file writable (".$log_file.")...");
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
     * @access private
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
		echo "\n".$msg."\n\n";
		
		// Trigger PHP error is flag passed
		if ($trigger_error) {
			trigger_error($msg);
		}
	}
}
