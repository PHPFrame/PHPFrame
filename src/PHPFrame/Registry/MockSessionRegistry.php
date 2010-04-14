<?php
/**
 * PHPFrame/Registry/MockSessionRegistry.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Registry
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * Mock Session Registry Class
 *
 * @category PHPFrame
 * @package  Registry
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
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

    /**
     * Constructor.
     *
     * @return void
     * @since  1.0
     */
    protected function __construct()
    {
        // Store session id in session array
        $this->data["id"] = session_id();

        // Acquire session user object
        $user = new PHPFrame_User();
        $user->id(1);
        $user->groupId(1);
        $user->email("test@localhost.local");
        $this->data["user"] = $user;

        // Acquire sysevents object
        $this->data["sysevents"] = new PHPFrame_Sysevents();

        // Generate session token
        $this->getToken(true);

        // Detect client for this session
        $this->detectClient();
    }

    /**
     * Get Instance
     *
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
