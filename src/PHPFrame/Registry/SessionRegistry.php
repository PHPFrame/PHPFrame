<?php
/**
 * PHPFrame/Registry/SessionRegistry.php
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
 * The Session Registry class produces a singleton object that encapsulates and
 * centralises access to session data.
 *
 * The session object is accessed from the PHPFrame facade class as follows:
 *
 * <code>
 * $session = PHPFrame::getSession();
 * </code>
 *
 * @category PHPFrame
 * @package  Registry
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @see      PHPFrame_Registry
 * @since    1.0
 */
class PHPFrame_SessionRegistry extends PHPFrame_Registry
{
    /**
     * Instance of itself in order to implement the singleton pattern
     *
     * @var object of type PHPFrame_SessionRegistry
     */
    private static $_instance = null;
    /**
     * A string used to name the session
     *
     * @var string
     */
    private $_session_name = "PHPFrame";
    /**
     * Cookie lifetime
     *
     * The time the cookie expires. This is a Unix timestamp so is in number of
     * seconds since the epoch.
     *
     * @var int
     */
    private $_cookie_lifetime = 0;
    /**
     * The path on the server in which the cookie will be available on. If set
     * to '/', the cookie will be available within the entire domain . If set
     * to '/foo/', the cookie will only be available within the /foo/ directory
     * and all sub-directories such as /foo/bar/ of domain .
     *
     * @var string
     */
    private $_cookie_path = "/";
    /**
     * The domain that the cookie is available. To make the cookie available on
     * all subdomains of example.com then you'd set it to '.example.com'.
     * The . is not required but makes it compatible with more browsers.
     *
     * @var string
     */
    private $_cookie_domain = null;
    /**
     * Indicates that the cookie should only be transmitted over a secure HTTPS
     * connection from the client. When set to TRUE, the cookie will only be set
     * if a secure connection exists.
     *
     * @var bool
     */
    private $_cookie_secure = false;
    /**
     * When TRUE the cookie will be made accessible only through the HTTP protocol.
     * This means that the cookie won't be accessible by scripting languages, such
     * as JavaScript. This setting can effectively help to reduce identity theft
     * through XSS attacks (although it is not supported by all browsers).
     *
     * @var bool
     */
    private $_cookie_httponly = true;
    /**
     * Array containing the session data
     *
     * @var array
     */
    private $_data = array();

    /**
     * Constructor
     *
     * @param string $base_url Base URL used for creating the cookie path and
     *                         domain.
     *
     * @return void
     * @since  1.0
     */
    protected function __construct($base_url)
    {
        // Get path and domain to use for cookie
        $uri                  = new PHPFrame_URI($base_url);
        $this->_cookie_path   = $uri->getDirname();
        $this->_cookie_domain = $uri->getHost();

        // Set custom session name
        ini_set("session.name", $this->_session_name);

        // Initialise cookie
        ini_set("session.cookie_lifetime", $this->_cookie_lifetime);
        ini_set("session.cookie_path", $this->_cookie_path);
        ini_set("session.cookie_secure", $this->_cookie_secure);
        ini_set("session.cookie_httponly", $this->_cookie_httponly);

        // start php session
        session_start();

        // Get reference to session super global
        $this->_data =& $_SESSION;

        // If new session we initialise
        if (!isset($this->_data['id']) || $this->_data['id'] != session_id()) {
            // Store session id in session array
            $this->_data['id'] = session_id();

            // Acquire session user object
            $this->_data['user'] = new PHPFrame_User();
            $this->_data['user']->id(0);
            $this->_data['user']->groupId(0);
            $this->_data['user']->email("guest@localhost.local");

            // Acquire sysevents object
            $this->_data['sysevents'] = new PHPFrame_Sysevents();

            // Generate session token
            $this->getToken(true);

            // Detect client for this session
            $this->detectClient();

        } elseif (
            isset($_SERVER["HTTP_X_API_USERNAME"])
            && isset($_SERVER["HTTP_X_API_SIGNATURE"])
            && !($this->_data['client'] instanceof PHPFrame_XMLRPCClient)
        ) {
            /*
             * If we are dealing with an api request that already has an
             * existing session but the client object is not set to XMLRPC we
             * instantiate a new client object replace it in the session, store
             * the old one in another var as well as the  user object so that
             * we can put them back in place when the next non-api request is
             * received
             */
            $this->_data['overriden_client'] = $this->_data['client'];
            $this->_data['overriden_user']   = $this->_data['user'];
            $this->_data['client']           = new PHPFrame_XMLRPCClient();

        } elseif (
            !isset($_SERVER["HTTP_X_API_USERNAME"])
            && !isset($_SERVER["HTTP_X_API_SIGNATURE"])
            && isset($this->_data['overriden_client'])
            && $this->_data['overriden_client'] instanceof PHPFrame_Client
        ) {
            // If we already have a session with an xmlrpc client object but no
            // api headers are included in request we then revert the client
            // and user objects
            $this->_data['client'] = $this->_data['overriden_client'];
            $this->_data['user']   = $this->_data['overriden_user'];
            unset($this->_data['overriden_client']);
            unset($this->_data['overriden_user']);
        }
    }

    /**
     * Get Instance
     *
     * @param string $base_url [Optional] Base URL used for creating the cookie
    *                          path and domain.
     *
     * @return PHPFrame_Registry
     * @since  1.0
     */
    public static function getInstance($base_url=null)
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self($base_url);
        }

        return self::$_instance;
    }

    /**
     * Implementation of the IteratorAggregate interface.
     *
     * @return ArrayIterator
     * @since  1.0
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_data);
    }

    /**
     * Get a session variable
     *
     * @param string $key           A string used to identify the session
     *                              variable we want to retrieve.
     * @param mixed  $default_value An optional default value to assign if
     *                              the given key is not set.
     *
     * @return mixed
     * @since  1.0
     */
    public function get($key, $default_value=null)
    {
        // Set default value if applicable
        if (!isset($this->_data[$key]) && !is_null($default_value)) {
            $this->_data[$key] = $default_value;
        }

        // If key is not set in session super global we return null
        if (!isset($this->_data[$key])) {
            return null;
        }

        return $this->_data[$key];
    }

    /**
     * Set a session variable
     *
     * @param string $key   A string used to identify the session variable we
     *                      want to set.
     * @param mixed  $value The value we want to store in the specified key.
     *
     * @return void
     * @since  1.0
     */
    public function set($key, $value)
    {
        $this->_data[$key] = $value;
    }

    /**
     * Get session id
     *
     * @return string
     * @since  1.0
     */
    public function getId()
    {
        return $this->_data['id'];
    }

    /**
     * Get session name
     *
     * @return string
     * @since  1.0
     */
    public function getName()
    {
        return $this->_session_name;
    }

    /**
     * Get client object
     *
     * @return PHPFrame_Environment_IClient|null
     * @since  1.0
     */
    public function getClient()
    {
        if (isset($this->_data['client'])
            && $this->_data['client'] instanceof PHPFrame_Client
        ) {
            return $this->_data['client'];
        }

        return null;
    }


    /**
     * Get client object's name
     *
     * @return string
     * @since  1.0
     */
    public function getClientName()
    {
        if (isset($this->_data['client'])
            && $this->_data['client'] instanceof PHPFrame_Client
        ) {
            return $this->_data['client']->getName();
        }

        return null;
    }

    /**
     * Set session user
     *
     * @param PHPFrame_User $user User object of type PHPFrame_User
     *
     * @return void
     * @since  1.0
     */
    public function setUser(PHPFrame_User $user)
    {
        $this->_data['user'] = $user;
    }

    /**
     * Get session's user object
     *
     * @return PHPFrame_User|null
     * @since  1.0
     */
    public function getUser()
    {
        if (isset($this->_data['user'])
            && $this->_data['user'] instanceof PHPFrame_User
        ) {
            return $this->_data['user'];
        }

        return null;
    }

    /**
     * Get session user id
     *
     * @return int
     * @since  1.0
     */
    public function getUserId()
    {
        if (isset($this->_data['user'])
            && $this->_data['user'] instanceof PHPFrame_User
        ) {
            return (int) $this->_data['user']->id();
        }

        return 0;
    }

    /**
     * Get session user groupid
     *
     * @return int
     * @since  1.0
     */
    public function getGroupId()
    {
        if (isset($this->_data['user'])
            && $this->_data['user'] instanceof PHPFrame_User
        ) {
            return (int) $this->_data['user']->groupId();
        }

        return 0;
    }

    /**
     * Is the current session authenticated?
     *
     * @return bool Returns TRUE if user is authenticated or FALSE otherwise.
     * @since  1.0
     */
    public function isAuth()
    {
        if (isset($this->_data['user'])
            && $this->_data['user'] instanceof PHPFrame_User
            && $this->_data['user']->id() > 0
        ) {
            return true;
        }

        return false;
    }

    /**
     * Is the current session an admin session?
     *
     * @return bool Returns TRUE if current user is admin or FALSE otherwise.
     * @since  1.0
     */
    public function isAdmin()
    {
        if ($this->isAuth()) {
            return ($this->_data['user']->groupId() == 1);
        }

        return false;
    }

    /**
     * Get system events object
     *
     * @return PHPFrame_Sysevents
     * @since  1.0
     */
    public function getSysevents()
    {
        if (isset($this->_data['sysevents'])
            && $this->_data['sysevents'] instanceof PHPFrame_Sysevents
        ) {
            return $this->_data['sysevents'];
        }

        return null;
    }

    /**
     * Get a session token, if a token isn't set yet one will be generated.
     *
     * Tokens are used to secure forms from spamming attacks. Once a token
     * has been generated the system will check the post request to see if
     * it is present, if not it will invalidate the session.
     *
     * @param bool $force_new If true, force a new token to be created
     *
     * @return string
     * @since  1.0
     */
    public function getToken($force_new=false)
    {
        //create a token
        if (!isset($this->_data['token']) || $force_new) {
            $this->_data['token'] = $this->_createToken(12);
        }

        return $this->_data['token'];
    }

    /**
     * Checks for a form token in the request
     *
     * @return bool TRUE if found and valid, FALSE otherwise
     * @since  1.0
     */
    public function checkToken()
    {
        $request_token = PHPFrame::Request()->getParam($this->getToken(), '');

        if ($request_token == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Destroy session
     *
     * @return void
     * @since  1.0
     */
    public function destroy()
    {
        // this destroys the session and generates a new session id
        session_regenerate_id(true);

        // Delete cookie. This has to be done using the same parameters
        // used when creating the cookie
        setcookie(
            $this->_session_name,
            "",
            time() - 3600,
            $this->_cookie_path,
            null,
            $this->_cookie_secure,
            $this->_cookie_httponly
        );
    }

    /**
     * Detect and set client object
     *
     * @return void
     * @since  1.0
     */
    protected function detectClient()
    {
        // Build array with available clients
        //TODO: This should be read from directory
        $available_clients = array("CLI", "XMLRPC", "Mobile", "Default");

        //loop through files
        foreach ($available_clients as $client) {
            //build class names
            $className = "PHPFrame_".$client."Client";
            if (is_callable(array($className, "detect"))) {
                //call class's detect() to check if this is the helper we need
                $client = call_user_func(array($className, "detect"));
                if ($client instanceof PHPFrame_Client) {
                    // store instance and break out of the function if we found
                    // our helper
                    $this->set("client", $client);
                    return;
                }
            }
        }

        //throw error if no helper is found
        throw new RuntimeException("ERROR: No client could be found!");
    }

    /**
     * Create a token-string
     *
     * @param int $length Lenght of string.
     *
     * @return string Generated token.
     * @since  1.0
     */
    private function _createToken($length = 32)
    {
        static $chars = '0123456789abcdef';

        $max   = strlen($chars) - 1;
        $token = '';
        $name  = session_name();

        for ($i=0; $i<$length; ++$i) {
            $token .= $chars[ (rand(0, $max)) ];
        }

        return md5($token.$name);
    }
}
