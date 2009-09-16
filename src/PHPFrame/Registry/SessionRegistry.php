<?php
/**
 * PHPFrame/Registry/SessionRegistry.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Registry
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * The Session Registry class produces a singleton object that encapsulates and 
 * centralises access to session data.
 * 
 * The session object is accessed from the PHPFrame facade class as follows:
 * 
 * <code>
 * $session = PHPFrame::Session();
 * </code>
 *
 * @category PHPFrame
 * @package  Registry
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see      PHPFrame_Registry
 * @uses     PHPFrame, PHPFrame_RequestRegistry, PHPFrame_URI, PHPFrame_User, 
 *           PHPFrame_Sysevents, PHPFrame_IClient, 
 *           PHPFrame_CLIClient, PHPFrame_DefaultClient, PHPFrame_XMLRPCClient, 
 *           PHPFrame_MobileClient
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
     * Constructor
     *
     * @access protected
     * @return void
     * @since  1.0
     */
    protected function __construct()
    {
        // Get path and domain to use for cookie
        $uri                  = new PHPFrame_URI();
        $this->_cookie_path   = $uri->getDirname()."/";
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
        
        // If new session we initialise
        if (!isset($_SESSION['id']) || $_SESSION['id'] != session_id()) {
            // Store session id in session array
            $_SESSION['id'] = session_id();
            
            // Acquire session user object
            $_SESSION['user'] = new PHPFrame_User();
            $_SESSION['user']->setId(0);
            $_SESSION['user']->setGroupId(0);
            
            // Acquire sysevents object
            $_SESSION['sysevents'] = new PHPFrame_Sysevents();
            
            // Generate session token
            $this->getToken(true);
            
            // Detect client for this session
            $this->_detectClient();
            
        } elseif (
            isset($_SERVER["HTTP_X_API_USERNAME"])
            && isset($_SERVER["HTTP_X_API_SIGNATURE"])
            && !($_SESSION['client'] instanceof PHPFrame_XMLRPCClient)
        ) {
            // If we are dealing with an api request that already has an existing session
            // but the client object is not set to XMLRPC we instantiate a new client object
            // replace it in the session, store the old one in another var as well as the 
            // user object so that we can put them back in place when the next non-api
            // request is received
            $_SESSION['overriden_client'] = $_SESSION['client'];
            $_SESSION['overriden_user']   = $_SESSION['user'];
            $_SESSION['client']           = new PHPFrame_XMLRPCClient();
            
        } elseif (
            !isset($_SERVER["HTTP_X_API_USERNAME"])
            && !isset($_SERVER["HTTP_X_API_SIGNATURE"])
            && isset($_SESSION['overriden_client']) 
            && $_SESSION['overriden_client'] instanceof PHPFrame_IClient
        ) {
            // If we already have a session with an xmlrpc client object but no api
            // headers are included in request we then revert the client and user objects
            $_SESSION['client'] = $_SESSION['overriden_client'];
            $_SESSION['user']   = $_SESSION['overriden_user'];
            unset($_SESSION['overriden_client']);
            unset($_SESSION['overriden_user']);
        }
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
    
    public function getIterator()
    {
        return new ArrayIterator($_SESSION);
    }

    /**
     * Get a session variable
     *
     * @param string $key           A string used to identify the session
     *                              variable we want to retrieve.
     * @param mixed  $default_value An optional default value to assign if
     *                              the given key is not set.
     *
     * @access public
     * @return mixed
     * @since  1.0
     */
    public function get($key, $default_value=null)
    {
        // Set default value if applicable
        if (!isset($_SESSION[$key]) && !is_null($default_value)) {
            $_SESSION[$key] = $default_value;
        }

        // If key is not set in session super global we return null
        if (!isset($_SESSION[$key])) {
            return null;
        }

        return $_SESSION[$key];
    }

    /**
     * Set a session variable
     *
     * @param string $key   A string used to identify the session variable we
     *                      want to set.
     * @param mixed  $value The value we want to store in the specified key.
     *
     * @access public
     * @return void
     * @since  1.0
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get session id
     *
     * @access public
     * @return string
     * @since  1.0
     */
    public function getId()
    {
        return $_SESSION['id'];
    }

    /**
     * Get session name
     *
     * @access public
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
     * @access public
     * @return PHPFrame_Environment_IClient|null
     * @since  1.0
     */
    public static function getClient()
    {
        if (
            isset($_SESSION['client'])
            && $_SESSION['client'] instanceof PHPFrame_IClient
        ) {
            return $_SESSION['client'];
        }

        return null;
    }


    /**
     * Get client object's name
     *
     * @access public
     * @return string
     * @since  1.0
     */
    public function getClientName()
    {
        if (
            isset($_SESSION['client'])
            && $_SESSION['client'] instanceof PHPFrame_IClient
        ) {
            return $_SESSION['client']->getName();
        }

        return null;
    }

    /**
     * Set session user
     *
     * @param PHPFrame_User $user User object of type PHPFrame_User
     *
     * @access public
     * @return void
     * @since  1.0
     */
    public function setUser(PHPFrame_User $user)
    {
        $_SESSION['user'] = $user;
    }

    /**
     * Get session's user object
     *
     * @access public
     * @return PHPFrame_User|null
     * @since  1.0
     */
    public function getUser()
    {
        if (
            isset($_SESSION['user'])
            && $_SESSION['user'] instanceof PHPFrame_User
        ) {
            return $_SESSION['user'];
        }

        return null;
    }

    /**
     * Get session user id
     *
     * @access public
     * @return int
     * @since  1.0
     */
    public function getUserId()
    {
        if (
            isset($_SESSION['user'])
            && $_SESSION['user'] instanceof PHPFrame_User
        ) {
            return (int) $_SESSION['user']->getId();
        }

        return 0;
    }

    /**
     * Get session user groupid
     *
     * @access public
     * @return int
     * @since  1.0
     */
    public function getGroupId()
    {
        if (
            isset($_SESSION['user'])
            && $_SESSION['user'] instanceof PHPFrame_User
        ) {
            return (int) $_SESSION['user']->getGroupId();
        }

        return 0;
    }

    /**
     * Is the current session authenticated?
     *
     * @access public
     * @return bool Returns TRUE if user is authenticated or FALSE otherwise.
     * @since  1.0
     */
    public function isAuth()
    {
        if (
            isset($_SESSION['user'])
            && $_SESSION['user'] instanceof PHPFrame_User
            && $_SESSION['user']->getId() > 0
        ) {
            return true;
        }

        return false;
    }

    /**
     * Is the current session an admin session?
     *
     * @access public
     * @return bool Returns TRUE if current user is admin or FALSE otherwise.
     * @since  1.0
     */
    public function isAdmin()
    {
        if ($this->isAuth()) {
            return ($_SESSION['user']->getGroupId() == 1);
        }

        return false;
    }

    /**
     * Get system events object
     *
     * @access public
     * @return PHPFrame_Sysevents
     * @since  1.0
     */
    public function getSysevents()
    {
        if (
            isset($_SESSION['sysevents'])
            && $_SESSION['sysevents'] instanceof PHPFrame_Sysevents
        ) {
            return $_SESSION['sysevents'];
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
     * @access public
     * @return string
     * @since  1.0
     */
    public function getToken($force_new=false)
    {
        //create a token
        if (!isset($_SESSION['token']) || $force_new) {
            $_SESSION['token'] = $this->_createToken(12);
        }

        return $_SESSION['token'];
    }

    /**
     * Checks for a form token in the request
     *
     * @access public
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
     * @access public
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
     * @access private
     * @return void
     * @since  1.0
     */
    private function _detectClient()
    {
        // Build array with available clients
        //TODO: This should be read from directory
        $available_clients = array("CLI", "Mobile", "XMLRPC", "Default");

        //loop through files
        foreach ($available_clients as $client) {
            //build class names
            $className = "PHPFrame_".$client."Client";
            if (is_callable(array($className, 'detect'))) {
                //call class's detect() to check if this is the helper we need
                $_SESSION["client"] = call_user_func(array($className, "detect"));
                if ($_SESSION["client"] instanceof PHPFrame_IClient) {
                    //break out of the function if we found our helper
                    return;
                }
            }
        }

        //throw error if no helper is found
        throw new RuntimeException(PHPFrame_Lang::SESSION_ERROR_NO_CLIENT_DETECTED);
    }

    /**
     * Create a token-string
     *
     *
     * @param int $length Lenght of string.
     *
     * @access private
     * @return string  Generated token.
     * @since  1.0
     */
    private function _createToken($length = 32)
    {
        static $chars = '0123456789abcdef';

        $max   = strlen( $chars ) - 1;
        $token = '';
        $name  = session_name();

        for($i=0; $i<$length; ++$i) {
            $token .= $chars[ (rand(0, $max)) ];
        }

        return md5($token.$name);
    }
}
