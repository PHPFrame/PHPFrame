<?php
/**
 * PHPFrame/Registry/MockSessionRegistry.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Registry
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Mock Session Registry Class
 * 
 * @category PHPFrame
 * @package  Registry
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 * @internal
 */
class PHPFrame_MockSessionRegistry extends PHPFrame_SessionRegistry
{
        /**
     * Instance of itself in order to implement the singleton pattern
     *
     * @var object of type PHPFrame_SessionRegistry
     */
    private static $_instance = null;

    
    protected function __construct()
    {
        // Store session id in session array
        $this->set('id', session_id());
            
        // Acquire session user object
        $user = new PHPFrame_User();
        $user->setId(1);
        $user->setGroupId(1);
        $user->setUserName("testuser");
        $this->set('user', $user);
            
        // Acquire sysevents object
        $this->set('sysevents', new PHPFrame_Sysevents());
            
        // Generate session token
        $this->getToken(true);
            
        // Detect client for this session
        $this->detectClient();
    }
    
    /**
     * Get Instance
     *
     * @static
     * @access public
     * @return PHPFrame_Registry
     * @since  1.0
     */
    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }
}