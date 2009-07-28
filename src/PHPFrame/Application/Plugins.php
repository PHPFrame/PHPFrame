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
     * Config object used to store plugin data
     *
     * @var PHPFrame_Config
     */
    private $_config=null;
    
    /**
     * Construct
     * 
     * @return void
     * @since  1.0
     */
    function __construct() 
    {
        $path = PHPFRAME_CONFIG_DIR.DS."plugins.xml";
        $this->_config = PHPFrame_Config::instance($path);
    }
    
    /**
     * Load plugin by name (ie: dashboard)
     * 
     * it loads properties from XML and returns an assoc array.
     * 
     * @param string $name The plugin name.
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function loadByName($name) 
    {
        foreach ($this->_config->toArray() as $plugin) {
            if ($plugin['name'] == $name) {
                return $plugin;
            }
        }
        
        return null;
    }
    
    /**
     * This methods tests whether the specified plugin is installed and enabled.
     *
     * @param string $name The plugin name to check (ie: dashboard, user, projects, ...)
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public static function isEnabled($name) 
    {
        foreach ($this->_config->toArray() as $plugin) {
            if ($plugin['name'] == $name && $plugin['enabled'] == 1) {
                return true;
            }
        }
        
        return false;
    }
}
