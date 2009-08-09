#!/usr/bin/env php
<?php
/**
 * scripts/post-install.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage PHPFrame_CLI
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id: PHPFrame_CLI_Tool.php 288 2009-07-23 01:31:09Z luis.montero@e-noise.com $
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * This class installs the CLI Tool application
 */
class PHPFrame_CLI_Tool_postinstall
{
	/**
     * Constructor
     * 
     * @return void
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
        // Include PHPFrame framework
        require_once "PHPFrame.php";
        
        if (!class_exists('PHPFrame')) {
			$this->_output("Missing PHPFrame. Please check your PEAR installation.");
			return false;
        }
        
        $this->_install_path = PEAR_INSTALL_DIR.DIRECTORY_SEPARATOR;
        $this->_install_path .= "PHPFrame_CLI_Tool";

		$msg = "\nPHPFrame CLI Tool installation";
		$msg .= "\n------------------------------\n\n";
		$msg .= "\nTo install please fill in all fields in order to install ";
		$msg .= "or type 'abort' to skip installation.\n\n";
		$msg .= "\nInstallation directory: ".$this->_install_path."\n\n";
        
		echo $msg;
		
		// Ensure writable installation directory
		try {
			PHPFrame_Utils_Filesystem::ensureWritableDir($this->_install_path);
		} catch (Exception $e) {
			$this->_output("Installation directory not writable...");
			$this->_output("Installation failed...");
		}
		
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
		// Only process if info array contains data
		if (!is_array($infoArray) || count($infoArray) < 1) {
			$this->_output("Installation skipped...");
			return false;
		}

		// Fetch PHPFrame_CLI_Tool source
        if (!$this->_fetchSource()) {
			$this->_output("Error getting PHPFrame_CLI_Tool source...");
			$this->_output("Installation failed...");
			return false;
		}
		
		// Create config file
        if (!$this->_createConfig($infoArray)) {
			$this->_output("Error creating config file...");
			$this->_output("Installation failed...");
			return false;
		}
		
		// If we got here installation succeded
		$this->_output("PHPFrame CLI Tool successfully installed...");
		return true;
    }
	
    private function _fetchSource()
    {
        $source = "http://phpframe.googlecode.com/svn/PHPFrame_AppTemplate/trunk/";
        $cmd = "svn export --force ".$source." ".$this->_install_path.DIRECTORY_SEPARATOR;
        
		$this->_output("Fetching PHPFrame_AppTemplate source from repository...");
		$this->_output("Using command \"".$cmd."\"...");
		
		$exec = PHPFrame_Utils_Exec::run($cmd);
		
		$this->_output($exec->getOutput());
		
		if ($exec->getReturnVar() > 0) {
			$this->_output("Failed to checkout source from repository...");
			
			return false;
		}
		
		return true;
    }
    
    private function _createConfig($array)
    {
        if (!is_array($array)) {
            $msg = get_class($this)."::_createConfig()";
            $msg .= " expected an array as only argument.";
            $this->_output($msg);
        }

		$this->_output("Creating configuration file...");
        
        // Instanciate new config object
        $config = PHPFrame_Config::instance();
        
        // Bind to array
        $config->bind($array);
        
        // Write to filesystem
        $config_file = $this->_install_path.DIRECTORY_SEPARATOR;
        $config_file .= "etc".DIRECTORY_SEPARATOR;
        $config_file .= "config.xml";
        PHPFrame_Utils_Filesystem::write($config_file, $config->toXML());

		return true;
    }

	private function _output($msg, $trigger_error=false)
	{
		// Convert messages in array format to string
		if (is_array($msg)) {
			$msg = implode("\n", $msg);
		}
		
		// Echo message to user
		echo "\n".$msg."\n\n";
		
		// Log message to file
		PHPFrame_Debug_Logger::write($msg);
		
		// Trigger PHP error is flag passed
		if ($trigger_error) {
			trigger_error($msg);
		}
	}
}
