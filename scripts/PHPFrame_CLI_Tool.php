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
 * @version    SVN: $Id: PHPFrame_CLI_Tool.php 220 2009-07-14 23:49:41Z luis.montero@e-noise.com $
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
    public function init(PEAR_Config $config , PEAR_PackageFile_v2 $self , $lastInstalledVersion=null)
    {
        // Include PHPFrame framework
        require_once "PHPFrame.php";
        
        if (!class_exists('PHPFrame')) {
            die("Missing PHPFrame. Please check your PEAR installation.\n");
        }
        
        $this->_install_path = PEAR_INSTALL_DIR.DIRECTORY_SEPARATOR;
        $this->_install_path .= "PHPFrame_CLI_Tool";
        
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
        // Fetch scaffold source
        $this->_fetchSource();
        
        // Create config file
        $this->_createConfig($infoArray);
    }
    
    private function _fetchSource()
    {
        $source = "http://phpframe.googlecode.com/svn/PHPFrame_Scaffold/trunk/";
        
        $cmd = "svn export ".$source." ".$this->_install_path.DIRECTORY_SEPARATOR;
        
        exec($cmd, $output, $return_var); 
    }
    
    private function _createConfig($array)
    {
        if (!is_array($array)) {
            $msg = get_class($this)."::_createConfig()";
            $msg .= " expected an array as only argument.";
            trigger_error($msg);
        }
        
        // Instanciate new config object
        $config = new PHPFrame_Config();
        
        // Bind to array
        $config->bind($array);
        
        // Write to filesystem
        $config_file = $this->_install_path.DIRECTORY_SEPARATOR;
        $config_file .= "etc".DIRECTORY_SEPARATOR;
        $config_file .= "config.xml";
        PHPFrame_Utils_Filesystem::write($config_file, $config->toXML());
    }
}
