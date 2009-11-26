<?php
/**
 * PHPFrame/Registry/RequestRegistry.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Registry
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * This class encapsulates access to the request arrays and provides input 
 * filtering.
 * 
 * The request class is responsible for processing the incoming request 
 * according to the current session's client.
 * 
 * The request object is accessed from the PHPFrame facade class as follows:
 * 
 * <code>
 * $session = PHPFrame::Request();
 * </code>
 * 
 * @todo This class needs to be changed to use PHPFrame_Filter instead of
 *       phpinputfilter
 *             
 * @category PHPFrame
 * @package  Registry
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @see      PHPFrame_Registry
 * @uses     InputFilter, PHPFrame, PHPFrame_SessionRegistry, PHPFrame_Config
 * @since    1.0
 */
class PHPFrame_RequestRegistry extends PHPFrame_Registry
{
    /**
     * Instance of PHPInputFilter
     * 
     * @var object
     */
    private $_inputfilter = null;
    /**
     * A unification array of filtered global arrays
     * 
     * @var array
     */
    private $_array = array(
        "controller"=>null,
        "action"=>null,
        "params"=>array(),
        "headers"=>array(),
        "remote_addr"=>null,
        "method"=>null,
        "query_string"=>null,
        "request_uri"=>null,
        "script_name"=>null,
        "request_time"=>null,
        "files"=>array(),
        "outfile"=>null,
        "quiet"=>false,
        "ajax"=>false
    );
    /**
     * Flag indicating whether the request has been dispatched
     * 
     * @var bool
     */
    private $_dispatched = false;
    
    /**
     * Constructor
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct() 
    {
        if (!isset(self::$_inputfilter)) {
            self::$_inputfilter = new InputFilter();
        }
        
        // Populate request array using session's client
        // Note that we dont use PHPFrame::getSession() as the globale
        // run level might not yet have been set to 2 (env initialised)
        $session = PHPFrame_SessionRegistry::getInstance();
        $session->getClient()->populateRequest($this);
        
        // If no controller has been set we use de default value provided in 
        // etc/phpframe.ini
        if (
            !isset($this->_array['controller']) 
            || empty($this->_array['controller'])
        )
        {
            $def_controller = PHPFrame::Config()->get("default_controller");
            $this->setControllerName($def_controller);
        }
    }
    
    public function __toString()
    {
        return print_r($this, true);
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
        return new ArrayIterator($this->_array);
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
        return $this->_array['controller'];
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
        $this->_array['controller'] = $value;
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
        return $this->_array['action'];
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
        $this->_array['action'] = self::$_inputfilter->process($value);
    }
    
    /**
     * Get request/post array
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getParams() 
    {
        return $this->_array['params'];
    }
    
    /**
     * Get a request variable
     * 
     * @param string $key
     * @param mixed  $def_value
     * 
     * @access public
     * @return mixed
     * @since  1.0
     */
    public function getParam($key, $def_value=null) 
    {
        if (!isset($this->_array['params'][$key]) && !is_null($def_value)) {
            $this->_array['params'][$key] = $def_value;
        }
        
        // Return null if index is not defined
        if (!isset($this->_array['params'][$key])) {
            return null;
        }
        
        return $this->_array['params'][$key];
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
    public function setParam($key, $value) 
    {
        $this->_array['params'][$key] = self::$_inputfilter->process($value);
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
        return $this->_array["headers"];
    }
    
    public function setHeader($key, $value)
    {
        $this->_array["headers"][$key] = $value;
    }
    
    public function getMethod()
    {
        return $this->_array['method'];
    }
    
    public function setMethod($str)
    {
        $this->_array['method'] = $str;
    }
    
    public function isPost()
    {
        return ($this->_array['method'] == "POST");
    }
    
    public function isGet()
    {
        return ($this->_array['method'] == "GET");
    }
    
    public function attachFile($key, array $array)
    {
        $this->_array["files"][$key] = $array;
    }
    
    public function dettachFile($key)
    {
        unset($this->_array["files"][$key]);
    }
    
    public function getFiles()
    {
        return $this->_array["files"];
    }
    
    public function getRemoteAddr()
    {
        return $this->_array["remote_addr"];
    }
    
    public function setRemoteAddr($str)
    {
        $this->_array["remote_addr"] = $str;
    }
    
    public function getRequestURI()
    {
        return $this->_array["request_uri"];
    }
    
    public function setRequestURI($str)
    {
        $this->_array["request_uri"] = $str;
    }
    
    public function getScriptName()
    {
        return $this->_array["script_name"];
    }
    
    public function setScriptName($str)
    {
        $this->_array["script_name"] = $str;
    }
    
    public function getQueryString()
    {
        return $this->_array["query_string"];
    }
    
    public function setQueryString($str)
    {
        $this->_array["query_string"] = $str;
    }
    
    public function getRequestTime()
    {
        return $this->_array["request_time"];
    }
    
    public function setRequestTime($str)
    {
        $this->_array["request_time"] = $str;
    }
    
    public function getOutfile()
    {
        return $this->_array["outfile"];
    }
    
    public function setOutfile($str)
    {
         $this->_array["outfile"] = $str;
    }
    
    public function isQuiet()
    {
        return $this->_array["quiet"];
    }
    
    public function setQuiet($bool)
    {
        $this->_array["quiet"] = (bool) $bool;
    }
    
    public function isAJAX()
    {
        return $this->_array["ajax"];
    }
    
    public function setAJAX($bool)
    {
        $this->_array["ajax"] = (bool) $bool;
    }
    
    public function isDispatched()
    {
        return $this->_dispatched;
    }
    
    public function setDispatched($bool)
    {
        $this->_dispatched = (bool) $bool;
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
        $this->_array      = array();
        $this->_dispatched = false;
    }
}
