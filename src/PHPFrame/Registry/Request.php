<?php
/**
 * PHPFrame/Registry/Request.php
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
 * This class encapsulates access to the request arrays and provides input filtering.
 * 
 * The request class is responsible for processing the incoming request according to 
 * the current session's client.
 * 
 * The request object is accessed from the PHPFrame facade class as follows:
 * 
 * <code>
 * $session = PHPFrame::Request();
 * </code>
 * 
 * @todo This class needs to be changed to use PHPFrame_Utils_Filter instead of
 *       phpinputfilter
 *             
 * @category PHPFrame
 * @package  Registry
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see      PHPFrame_Registry
 * @uses     InputFilter, PHPFrame, PHPFrame_Registry_Session, PHPFrame_Config
 * @since    1.0
 */
class PHPFrame_Registry_Request extends PHPFrame_Registry
{
    /**
     * Instance of itself in order to implement the singleton pattern
     * 
     * @var PHPFrame_Registry_Request
     */
    private static $_instance = null;
    /**
     * Instance of PHPInputFilter
     * 
     * @var object
     */
    private static $_inputfilter = null;
    /**
     * A unification array of filtered global arrays
     * 
     * @var array
     */
    private $_array = array();
    
    /**
     * Constructor
     * 
     * @access protected
     * @return void
     * @since  1.0
     */
    protected function __construct() 
    {
        if (!isset(self::$_inputfilter)) {
            self::$_inputfilter = new InputFilter();
        }
        
        // Populate request array using session's client
        // Note that we dont use PHPFrame::Session() as the globale
        // run level might not yet have been set to 2 (env initialised)
        $session = PHPFrame_Registry_Session::getInstance();
        $this->_array = $session->getClient()->populateRequest();
        
        //add other globals
        $this->_array['files']  = $_FILES;
        $this->_array['env']    = $_ENV;
        $this->_array['server'] = $_SERVER;
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
     * Get a request variable
     * 
     * @param string $key
     * @param mixed  $default_value
     * 
     * @access public
     * @return mixed
     * @since  1.0
     */
    public function get($key, $default_value=null) 
    {
        if (!isset($this->_array['request'][$key]) && !is_null($default_value)) {
            $this->_array['request'][$key] = $default_value;
        }
        
        // Return null if index is not defined
        if (!isset($this->_array['request'][$key])) {
            return null;
        }
        
        return $this->_array['request'][$key];
    }
    
    /**
     * Set a request variable
     * 
     * @param string $key
     * @param mixed  $value
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function set($key, $value) 
    {
        $this->_array['request'][$key] = self::$_inputfilter->process($value);
    }
    
    /**
     * Get request headers
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getHeaders()
    {
        $headers = array();
        
        if (isset($this->_array['server']) && is_array($this->_array['server'])) {
            foreach ($this->_array['server'] as $k => $v) {
                if (substr($k, 0, 5) == "HTTP_") {
                    $k = str_replace('_', ' ', substr($k, 5));
                    $k = str_replace(' ', '-', ucwords(strtolower($k)));
                    $headers[$k] = $v;
                }
            }
        }
        
        return $headers;
    }
    
    /**
     * Get request/post array from URA
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getPost() 
    {
        return $this->_array['request'];
    }
    
    /**
     * Get controller name
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getControllerName() 
    {
        // If controller has not been set we return the default value
        if (empty($this->_array['request']['controller'])) {
            $this->_array['request']['controller'] = PHPFrame::Config()->get("default_controller");
        }
        
        return $this->_array['request']['controller'];
    }
    
    /**
     * Set controller name
     * 
     * @param string $value The value to set the variable to.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setControllerName($value) 
    {
        $this->set('controller', $value);
    }
    
    /**
     * Get action name
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getAction() 
    {   
        return $this->get('action');
    }
    
    /**
     * Set $_action.
     * 
     * @param string $value The value to set the variable to.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setAction($value) 
    {
        // Filter value before assigning to variable
        $this->_array['request']['action'] = self::$_inputfilter->process($value);
    }
    
    /**
     * Destroy the request data
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function destroy() 
    {
        $this->_array = array();
    }
}
