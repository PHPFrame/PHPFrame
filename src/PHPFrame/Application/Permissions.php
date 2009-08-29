<?php
/**
 * PHPFrame/Application/Permissions.php
 * 
 * PHP version 5
 * 
 * @category PHPFrame
 * @package    PHPFrame
 * @subpackage Application
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Permissions Class
 * 
 * @category PHPFrame
 * @package    PHPFrame
 * @subpackage Application
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_Application_Permissions
{
    /**
     * A mapper object used to store and retrieve access level list
     *
     * @var PHPFrame_Mapper_Collection
     */
    private $_mapper;
    /**
     * A collection object holding access level list
     *
     * @var PHPFrame_Mapper_Collection
     */
    private $_acl;
    
    /**
     * Constructor
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct() 
    {
        // Get ACL from file
        $this->_mapper = new PHPFrame_Mapper(
            "PHPFrame_Application_ACL", 
            "acl", 
            PHPFrame_Mapper::STORAGE_XML, 
            false, 
            PHPFRAME_CONFIG_DIR
        );
        
        $acl = new PHPFrame_Application_ACL(array(
            "groupid"=>1, 
            "controller"=>"dummy", 
            "action"=>"*", 
            "value"=>"all"
        ));
        
        $this->_acl = $this->_mapper->find();
    }
    
    /**
     * Authorise action in a component for a given user group
     * 
     * @param string $controller The controller we want to authorise
     * @param string $action     The action we want to authorise
     * @param int    $groupid    The groupid of the user we want to authorise
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
        
        $ignore_acl = PHPFrame::Config()->get("ignore_acl");
        if ($ignore_acl == 1) {
            return true;
        }
        
        foreach ($this->_acl as $acl) {
            if ($acl->getGroupid() == $groupid 
                && $acl->getController() == $controller 
                && ($acl->getAction() == $action || $acl->getAction() == '*')
            ) {
                return true;
            }
        }
        
        return false;
    }
}
