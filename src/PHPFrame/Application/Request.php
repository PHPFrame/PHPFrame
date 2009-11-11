<?php
/**
 * PHPFrame/Application/Request.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Application
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * This class encapsulates a request made to a PHPFrame_Application
 *             
 * @category PHPFrame
 * @package  Application
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_Request implements IteratorAggregate
{
    /**
     * Array holding request data
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
     * @return void
     * @since  1.0
     */
    public function __construct() 
    {
        
    }
    
    /**
     * Convert object to string
     * 
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        return print_r($this, true);
    }
    
    /**
     * Implementation of IteratorAggregate interface
     * 
     * @return ArrayIterator
     * @since  1.0
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_array);
    }
    
    /**
     * Get controller name
     * 
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
     * @param string $str The value to set the variable to.
     * 
     * @return void
     * @since  1.0
     */
    public function setControllerName($str) 
    {
        $filter = new PHPFrame_RegexpFilter(array(
            "regexp"     => '/^[a-z_]+$/', 
            "min_length" => 1, 
            "max_length" => 50
        ));
        
        $this->_array['controller'] = $filter->process($str);
    }
    
    /**
     * Get action name
     * 
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
     * @param string $str The value to set the variable to.
     * 
     * @return void
     * @since  1.0
     */
    public function setAction($str) 
    {
        // Filter value before assigning to variable
        $filter = new PHPFrame_RegexpFilter(array(
            "regexp"     => '/^[a-z_]+$/', 
            "min_length" => 1, 
            "max_length" => 50
        ));
        
        $this->_array['action'] = $filter->process($str);
    }
    
    /**
     * Get request/post array
     * 
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
     * @return mixed
     * @since  1.0
     */
    public function getParam($key, $def_value=null) 
    {
        $key = trim((string) $key);
        
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
     * @return void
     * @since  1.0
     */
    public function setParam($key, $value) 
    {
        $key = trim((string) $key);
        
        $this->_array['params'][$key] = $value;
    }
    
    /**
     * Get request headers
     * 
     * @return array
     * @since  1.0
     */
    public function getHeaders()
    {
        return $this->_array["headers"];
    }
    
    /**
     * Set a request header
     * 
     * @param string $key
     * @param string $value
     * 
     * @return void
     * @since  1.0
     */
    public function setHeader($key, $value)
    {
        $key   = trim((string) $key);
        $value = trim((string) $value);
        
        $this->_array["headers"][$key] = $value;
    }
    
    public function getMethod()
    {
        return $this->_array['method'];
    }
    
    /**
     * Set the request method
     * 
     * @param string $str Allowed values are "GET", "POST" and "CLI".
     * 
     * @return void
     * @since  1.0
     */
    public function setMethod($str)
    {
        // Filter value before assigning to variable
        $filter = new PHPFrame_RegexpFilter(array(
            "regexp"     => '/^(GET|POST|CLI)$/', 
            "min_length" => 3, 
            "max_length" => 4
        ));
        
        $this->_array['method'] = $filter->process($str);
    }
    
    /**
     * Is the request method POST?
     * 
     * @return bool
     * @since  1.0
     */
    public function isPost()
    {
        return ($this->_array['method'] == "POST");
    }
    
    /**
     * Is the request method GET?
     * 
     * @return bool
     * @since  1.0
     */
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
    
    /**
     * Get files attached to request
     * 
     * @return array
     * @since  1.0
     */
    public function getFiles()
    {
        return $this->_array["files"];
    }
    
    /**
     * Get remote address (IP)
     * 
     * @return string
     * @since  1.0
     */
    public function getRemoteAddr()
    {
        return $this->_array["remote_addr"];
    }
    
    /**
     * Set the request remote address (IP).
     * 
     * @param string $str
     * 
     * @return void
     * @since  1.0
     */
    public function setRemoteAddr($str)
    {
        $this->_array["remote_addr"] = $str;
    }
    
    /**
     * Get request URI
     * 
     * @return string
     * @since  1.0
     */
    public function getRequestURI()
    {
        return $this->_array["request_uri"];
    }
    
    /**
     * Set the request URI
     * 
     * @param string $str
     * 
     * @return void
     * @since  1.0
     */
    public function setRequestURI($str)
    {
        $this->_array["request_uri"] = $str;
    }
    
    /**
     * Get request script name
     * 
     * @return string
     * @since  1.0
     */
    public function getScriptName()
    {
        return $this->_array["script_name"];
    }
    
    /**
     * Set the request script name
     * 
     * @param string $str
     * 
     * @return void
     * @since  1.0
     */
    public function setScriptName($str)
    {
        $this->_array["script_name"] = $str;
    }
    
    /**
     * Get request query string
     * 
     * @return string
     * @since  1.0
     */
    public function getQueryString()
    {
        return $this->_array["query_string"];
    }
    
    /**
     * Set the request query string
     * 
     * @param string $str
     * 
     * @return void
     * @since  1.0
     */
    public function setQueryString($str)
    {
        $this->_array["query_string"] = $str;
    }
    
    /**
     * Get the request time (unix timestamp)
     * 
     * @return int
     * @since  1.0
     */
    public function getRequestTime()
    {
        return $this->_array["request_time"];
    }
    
    /**
     * Set the request time (unix timestamp)
     * 
     * @param int $int
     * 
     * @return void
     * @since  1.0
     */
    public function setRequestTime($int)
    {
        $this->_array["request_time"] = (int) $int;
    }
    
    /**
     * Get output file absolute path
     * 
     * @return string
     * @since  1.0
     */
    public function getOutfile()
    {
        return $this->_array["outfile"];
    }
    
    /**
     * Set absolute path for file to write output. If not set no output will 
     * not be written to file, which is the normal behaviour.
     * 
     * @param string $str
     * 
     * @return void
     * @since  1.0
     */
    public function setOutfile($str)
    {
         $this->_array["outfile"] = (string) $str;
    }
    
    /**
     * Is Quiet request?
     * 
     * @return bool
     * @since  1.0
     */
    public function isQuiet()
    {
        return $this->_array["quiet"];
    }
    
    /**
     * Set whether the request should be handled in "quiet" mode (no output)
     * 
     * @param bool $bool
     * 
     * @return void
     * @since  1.0
     */
    public function setQuiet($bool)
    {
        $this->_array["quiet"] = (bool) $bool;
    }
    
    /**
     * Is AJAX request?
     * 
     * @return bool
     * @since  1.0
     */
    public function isAJAX()
    {
        return $this->_array["ajax"];
    }
    
    /**
     * Set whether request is AJAX.
     * 
     * @param bool $bool
     * 
     * @return void
     * @since  1.0
     */
    public function setAJAX($bool)
    {
        $this->_array["ajax"] = (bool) $bool;
    }
    
    /**
     * Has request already been dispatched?
     * 
     * @return bool
     * @since  1.0
     */
    public function isDispatched()
    {
        return $this->_dispatched;
    }
    
    /**
     * Set dispatched flag
     * 
     * @param bool $bool
     * 
     * @return void
     * @since  1.0
     */
    public function setDispatched($bool)
    {
        $this->_dispatched = (bool) $bool;
    }
}
