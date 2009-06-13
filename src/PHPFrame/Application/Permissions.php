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
     * Access level list loaded from database.
     * 
     * @var array
     */
    private $_acl=array();
    
    /**
     * Constructor
     * 
     * @access    public
     * @since     1.0
     */
    public function __construct() 
    {
        // Load ACL from DB
        $this->_acl = $this->_loadACL();
    }
    
    /**
     * Authorise action in a component for a given user group
     * 
     * @access    public
     * @param    string    $component
     * @param    string    $action
     * @param    int        $groupid
     * @return    bool
     * @since    1.0
     */
    public function authorise($component, $action, $groupid) 
    {
        foreach ($this->_acl as $acl) {
            if (
                $acl->groupid == $groupid 
                && $acl->component == $component 
                && ($acl->action == $action || $acl->action == '*')
               ) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Load access levels from database
     * 
     * @access    private
     * @return    array    An array ob database row objects
     * @since    1.0
     */
    private function _loadACL() 
    {
        // Load access list from DB
        $query = "SELECT * FROM #__acl_groups";
        return PHPFrame::DB()->loadObjectList($query);
    }
}
