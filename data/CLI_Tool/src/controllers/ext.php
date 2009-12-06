<?php
/**
 * data/CLITool/src/controllers/ext.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   PHPFrame_CLITool
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * Extensions controller.
 * 
 * @category PHPFrame
 * @package  PHPFrame_CLITool
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since    1.0
 */
class ExtController extends PHPFrame_ActionController
{
    private $_ext_types=array("feature", "theme", "plugin", "lib");
    private $_install_dir=null;
    private $_config=null;
    
    public function __construct($install_dir=null)
    {
        if (is_null($install_dir)) {
            $this->_install_dir = getcwd();
        } else {
            $this->_install_dir = (string) trim($install_dir);
        }
        
        $config_path   = $this->_install_dir.DS."etc".DS."phpframe.ini";
        $this->_config = PHPFrame_Config::instance($config_path);
        
        parent::__construct("install");
    }
    
    public function install($package)
    {
        $package = trim($package);
        
        try {
            $ext_installer = $this->getModel(
                "ExtInstaller", 
                array($this->_install_dir)
            );
            
            $ext_installer->install($package);
            
        } catch (Exception $e) {
            $this->raiseError($e->getMessage());
        }
        
        $this->getView()->display();
    }
    
    public function upgrade($package)
    {
        echo "This should upgrade a given extension";
    }
    
    public function remove($package, $ext_type="feature")
    {
        if (!in_array($ext_type, $this->_ext_types)) {
            $msg  = "Extension type not recognised. Argument ext_type must be ";
            $msg .= "one of the following values: '";
            $msg .= implode("','", $this->_ext_types)."'.";
            throw new InvalidArgumentException($msg);
        }
        
        $mapper = new PHPFrame_Mapper(
            "PHPFrame_".ucfirst($ext_type)."Info",
            $ext_type."s",
            PHPFrame_Mapper::STORAGE_XML,
            false,
            $this->_install_dir.DS."etc"
        );
        
        foreach ($mapper->find() as $ext) {
            if ($ext->getName() == $package) {
                break;
            }
        }
        
        print_r($ext);
        
        // Remove files
        
        // Run uninstall script
        
    }
    
    public function list_installed($ext_type)
    {
        echo "This should show a list of installed extensions";
    }
    
    public function list_available($ext_type, $server=null)
    {
        echo "This should show a list of available extensions";
    }
}
