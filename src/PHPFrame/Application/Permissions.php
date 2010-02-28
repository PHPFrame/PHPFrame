<?php
/**
 * PHPFrame/Application/Permissions.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Application
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * Permissions Class
 * 
 * @category PHPFrame
 * @package  Application
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_Permissions
{
    /**
     * A mapper object used to store and retrieve access level list.
     *
     * @var PHPFrame_PersistentObjectCollection
     */
    private $_mapper;
    /**
     * A collection object holding access level list.
     *
     * @var PHPFrame_PersistentObjectCollection
     */
    private $_acl;
    
    /**
     * Constructor.
     * 
     * @param PHPFrame_Mapper $mapper Mapper object used to persist the ACL 
     *                                objects.
     * 
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Mapper $mapper) 
    {
        $this->_mapper = $mapper;
        
        // Get ACL using mapper
        $this->_acl = $this->_mapper->find();
    }
    
    /**
     * Authorise action in a component for a given user group
     * 
     * @param string $controller The controller we want to authorise
     * @param string $action     The action we want to authorise
     * @param int    $groupid    The groupid of the user we want to authorise
     * 
     * @return bool
     * @since  1.0
     */
    public function authorise($controller, $action, $groupid) 
    {
        // Bypass auth for admin users
        if ($groupid == 1) {
            return true;
        }
        
        foreach ($this->_acl as $acl) {
            if ($acl->groupid() == $groupid 
                && $acl->controller() == $controller 
                && ($acl->action() == $action || $acl->action() == '*')
            ) {
                return true;
            }
        }
        
        return false;
    }
}
