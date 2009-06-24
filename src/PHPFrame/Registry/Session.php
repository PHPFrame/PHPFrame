<?php
/**
 * PHPFrame/Registry/Session.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Registry
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Session Class
 * 
 * The Session class is responsible for detecting the client (default, mobile, cli or xmlrpc)
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Registry
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Registry_Session extends PHPFrame_Registry
{
    /**
     * Instance of itself in order to implement the singleton pattern
     * 
     * @var object of type PHPFrame_Registry_Session
     */
    private static $_instance=null;
    
    /**
     * Constructor
     * 
     * @access protected
     * @return void
     * @since  1.0
     */
    protected function __construct() 
    {
        // Set custom session name
        ini_set("session.name", "PHPFrame");
        
        // Initialise cookie
        $expire = 0;
        $uri = new PHPFrame_Utils_URI();
        $path = $uri->getDirname()."/";
        $secure = false;
        $httponly = true;
        ini_set("session.cookie_lifetime", $expire);
        ini_set("session.cookie_path", $path);
        ini_set("session.cookie_secure", $secure);
        ini_set("session.cookie_httponly", $httponly);
        
        // start php session
        session_start();
        
        //$this->destroy(); exit;
        
        // If new session we initialise
        if (!isset($_SESSION['id']) || $_SESSION['id'] != session_id()) {
            // Store session id in session array
            $_SESSION['id'] = session_id();
            
            // Acquire session user object
            $_SESSION['user'] = new PHPFrame_User();
            $_SESSION['user']->set("id", 0);
            $_SESSION['user']->set("groupid", 0);
            
            // Acquire sysevents object
            $_SESSION['sysevents'] = new PHPFrame_Application_Sysevents();
            
            // Generate session token
            $this->getToken(true);
            
            // Detect client for this session
            $this->_detectClient();
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
     * Get client object
     * 
     * @access public
     * @return PHPFrame_Environment_IClient|null
     * @since  1.0
     */
    public static function getClient() 
    {   
        return $_SESSION['client'];
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
        return $_SESSION['client']->getName();
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
     * @return PHPFrame_User
     * @since  1.0
     */
    public function getUser() 
    {
        return $_SESSION['user'];
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
        return (int) $_SESSION['user']->get('id');
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
        return (int) $_SESSION['user']->get('groupid');
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
        return ($_SESSION['user']->get('id') > 0);
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
        return ($_SESSION['user']->get('groupid') == 1);
    }
    
    /**
     * Get system events object
     * 
     * @access public
     * @return PHPFrame_Application_Sysevents
     * @since  1.0
     */
    public function getSysevents() 
    {
        return $_SESSION['sysevents'];
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
            $className = 'PHPFrame_Client_'.$client;
            if (is_callable(array($className, 'detect'))) {
                //call class's detect() to check if this is the helper we need 
                $_SESSION['client'] = call_user_func(array($className, 'detect'));
                if ($_SESSION['client'] instanceof PHPFrame_Client_IClient) {
                    //break out of the function if we found our helper
                    return;
                } 
            }
        }
        
        //throw error if no helper is found
        throw new PHPFrame_Exception(_PHPFRAME_LANG_SESSION_ERROR_NO_CLIENT_DETECTED);
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
        $max = strlen( $chars ) - 1;
        $token = '';
        $name = session_name();
        
        for($i=0; $i<$length; ++$i) {
            $token .= $chars[ (rand( 0, $max )) ];
        }

        return md5($token.$name);
    }
}
