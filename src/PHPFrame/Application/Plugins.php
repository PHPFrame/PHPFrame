<?php
/**
 * PHPFrame/Application/Plugins.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Application
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Plugins Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Application
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Application_Plugins
{
    /**
     * A mapper object used to store and retrieve plugin data
     *
     * @var PHPFrame_Mapper_Collection
     */
    private $_mapper;
    /**
     * A collection object holding data about installed plugins
     *
     * @var PHPFrame_Mapper_Collection
     */
    private $_plugins;
    
    /**
     * Construct
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct() 
    {
        // Get installed plugins from file
        $this->_mapper = new PHPFrame_Mapper(
            "PHPFrame_Addons_PluginInfo", 
            "plugins", 
            PHPFrame_Mapper::STORAGE_XML, 
            false, 
            PHPFRAME_CONFIG_DIR
        );
        
        $this->_plugins = $this->_mapper->find();
    }
    
    public function install($name)
    {
        //$plugins_mapper->insert(new PHPFrame_Addons_Plugin());
    }
    
    public function uninstall($name)
    {
        
    }
    
    /**
     * Get plugin info by name
     * 
     * @param string $name The plugin name.
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getInfo($name) 
    {
        foreach ($this->_plugins as $plugin) {
            if ($plugin->getName() == $name) {
                return $plugin;
            }
        }
        
        $msg = "Plugin '".$name."' is not installed";
        throw new PHPFrame_Exception($msg);
    }
    
    /**
     * This methods tests whether the specified plugin is installed and enabled.
     *
     * @param string $name The plugin name to check (ie: dashboard, user, 
     *                     projects, ...)
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function isEnabled($name) 
    {
        foreach ($this->_plugins as $plugin) {
            if ($plugin->getName() == $name && $plugin->isEnabled()) {
                return true;
            }
        }
        
        return false;
    }
    
    public function isInstalled($name)
    {
        foreach ($this->_plugins as $plugin) {
            if ($plugin->getName() == $name && $plugin->isInstalled()) {
                return true;
            }
        }
        
        return false;
    }
}
