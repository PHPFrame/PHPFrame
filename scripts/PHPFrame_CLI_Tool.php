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
 * @version    SVN: $Id: PHPFrame_CLI_Tool.php 254 2009-07-16 00:42:13Z luis.montero@e-noise.com $
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
		
		// Check input array for db details
		if (!$this->_checkDBInput($infoArray)) {
			$this->_output("Not enough info to set up database...");
			$this->_output("Installation failed...");
			return false;
		}
		
		// Create db and dbuser if requested
		if (isset($infoArray["DB_ROOT"]) && isset($infoArray["DB_ROOT_PASS"])) {
			$concrete_dsn_class = "PHPFrame_Database_DSN_".$infoArray["DB_DRIVER"];
			$dsn = new $concrete_dsn_class($infoArray["DB_HOST"], "mysql");
			
			$db = PHPFrame::DB($dsn, $infoArray["DB_ROOT"], $infoArray["DB_ROOT_PASS"]);
			
			$sql_array[] = "CREATE USER '".$infoArray["DB_USER"]."'@'localhost' IDENTIFIED BY '".$infoArray["DB_PASS"]."'";
			$sql_array[] = "GRANT USAGE ON * . * TO '".$infoArray["DB_USER"]."'@'localhost' IDENTIFIED BY '".$infoArray["DB_PASS"]."'";
			$sql_array[] = "CREATE DATABASE `".$infoArray["DB_NAME"]."` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci";
			$sql_array[] = "GRANT ALL PRIVILEGES ON `".$infoArray["DB_NAME"]."` . * TO '".$infoArray["DB_NAME"]."'@'localhost'";
			
			foreach ($sql_array as $sql) {
				$db->query($sql);
			}
		}
		
		// Check DSN database
		
		// Populate DB
		
		
		// Fetch scaffold source
        if (!$this->_fetchSource()) {
			$this->_output("Error getting PHPFrame_Scaffold source...");
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
    
	private function _createDB()
	{
		
	}

	private function _checkDSN($driver, $host)
	{
		
	}
	
	private function _populateDB()
	{
		
	}
	
    private function _fetchSource()
    {
        $source = "http://phpframe.googlecode.com/svn/PHPFrame_Scaffold/trunk/";
        $cmd = "svn export --force ".$source." ".$this->_install_path.DIRECTORY_SEPARATOR;
        
		$this->_output("Fetching PHPFrame_Scaffold source from repository...");
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
	
	private function _checkDBInput($array)
	{
		$keys = array("DB_DRIVER", "DB_HOST", "DB_USER", "DB_PASS", "DB_NAME");
		
		foreach ($keys as $key) {
			if (!isset($array[$key])) {
				return false;
			}
		}
		
		return true;
	}
}
