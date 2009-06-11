<?php
/**
 * PHPFrame/Application/Components.php
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
 * Components Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Application
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Application_Components
{
    /**
     * Array containing the installed components
     * 
     * @var array
     */
    private $_array=array();
    
    /**
     * Construct
     * 
     * @return    void
     * @since    1.0
     */
    function __construct() 
    {
        $query = "SELECT * FROM #__components ";
        $this->_array = PHPFrame::getDB()->loadObjectList($query);
    }
    
    /**
     * Load component by option (ie: com_dashboard)
     * 
     * it loads properties from database table and returns the row object.
     * 
     * @param    string    $option The option string.
     * @return    object
     */
    public function loadByOption($option) 
    {
        foreach ($this->_array as $component) {
            if ($component->name == substr($option, 4)) {
                return $component;
            }
        }
        
        return null;
    }
    
    /**
     * This methods tests whether the specified component is installed and enabled.
     *
     * @access public
     * @param string $name The component name to check (ie: dashboard, user, projects, ...)
     * @return bool
     */
    public static function isEnabled($name) 
    {
        foreach ($this->_array as $component) {
            if ($component->name == substr($name, 4) && $component->enabled == 1) {
                return true;
            }
        }
        
        return false;
    }
}
