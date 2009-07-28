<?php
/**
 * PHPFrame/Application/Permissions.php
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
 * Permissions Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Application
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Application_Permissions
{
    /**
     * Path to xml file with acl
     * 
     * @var string
     */
    private $_path=null;
    /**
     * Access level list loaded from database.
     * 
     * @var array
     */
    private $_acl=array();
    
    /**
     * Constructor
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct() 
    {
        $this->_path = PHPFRAME_CONFIG_DIR.DS."acl.xml";
        
        // Load ACL from file
        $this->_loadACL();
    }
    
    /**
     * Authorise action in a component for a given user group
     * 
     * @param string $component The component we want to authorise
     * @param string $action    The action we want to authorise
     * @param int    $groupid   The groupid of the user we want to authorise
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function authorise($controller, $action, $groupid) 
    {
        // Bypass auth for admin users
        if ($groupid == 1) {
            return true;
        }
        
        $ignore_acl = PHPFrame::Config()->get("IGNORE_ACL");
        if ($ignore_acl == 1) {
            return true;
        }
        
        foreach ($this->_acl as $acl) {
            if ($acl['groupid'] == $groupid 
                && $acl['controller'] == $controller 
                && ($acl['action'] == $action || $acl['action'] == '*')
            ) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Load access levels from file
     * 
     * @access private
     * @return array   An array with config data
     * @since  1.0
     */
    private function _loadACL() 
    {
        // Read ACL from file
        $xml = @simplexml_load_file($this->_path);
        
        if ($xml instanceof SimpleXMLElement) {
            foreach ($xml->data as $data) {
                $array["groupid"] = trim((string) $data->groupid);
                $array["controller"] = trim((string) $data->controller);
                $array["action"] = trim((string) $data->action);
    			$array["value"] = trim((string) $data->value);
                
                $this->_acl[] = $array;
            }
        }
    }
}
