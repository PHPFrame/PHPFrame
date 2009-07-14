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
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

class scripts_PHPFrame_install_postinstall
{
    public function __construct()
    {
        echo "I'm the post-install script and should set up the phpframe 
	  		 commmand line utility as a PHPFrame app (i need a config file!! a db and so on!)";
    }
    
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
     * @since 1.0
     */
    public function init(PEAR_Config $config , PEAR_PackageFile_v2 $self , $lastInstalledVersion=null)
    {
        //echo "\n--"; var_dump($config); echo "\n";
        //echo "\n--"; var_dump($self); echo "\n";
        //echo "\n--"; var_dump($lastInstalledVersion); echo "\n";
        
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
     * @since 1.0
     */
    public function run($infoArray, $paramGroupId)
    {
        //echo "\n--"; var_dump($infoArray); echo "\n";
        //echo "\n--"; var_dump($paramGroupId); echo "\n";
    }
}
