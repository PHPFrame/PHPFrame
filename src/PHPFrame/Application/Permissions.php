<?php
/**
 * @version       SVN: $Id$
 * @package       PHPFrame
 * @subpackage    application
 * @copyright     2009 E-noise.com Limited
 * @license       http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Permissions Class
 *
 * @package        PHPFrame
 * @subpackage     application
 * @since         1.0
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
        return PHPFrame::getDB()->loadObjectList($query);
    }
}
