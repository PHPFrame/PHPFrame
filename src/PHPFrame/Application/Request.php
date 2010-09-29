<?php
/**
 * PHPFrame/Application/Request.php
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
 * This class encapsulates a request made to a {@link PHPFrame_Application}.
 *
 * @category PHPFrame
 * @package  Application
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
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
    private $_raw_body;

    /**
     * Constructor
     *
     * @return void
     * @since  1.0
     */
    public function __construct()
    {
        $this->_raw_body = file_get_contents("php://input");
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
     * Get/set controller name.
     *
     * @param string $str [Optional] The value to set the controller name to.
     *
     * @return string
     * @since  1.0
     */
    public function controllerName($str=null)
    {
        if (!is_null($str)) {
            $filter = new PHPFrame_RegexpFilter(array(
                "regexp"     => '/^[a-z_\-]+$/',
                "min_length" => 1,
                "max_length" => 50
            ));

            if (!$this->_array['controller'] = $filter->process($str)) {
                $msg  = "Invalid controller name '".$str."'. Controller ";
                $msg .= "names can only contain alphabetic characters plus ";
                $msg .= "an underscore.";
                throw new InvalidArgumentException($msg);
            }
        }

        return $this->_array['controller'];
    }

    /**
     * Get action name.
     *
     * @param string $str [Optional] The value to set the action to.
     *
     * @return string
     * @since  1.0
     */
    public function action($str=null)
    {
        if (!is_null($str)) {
            // Filter value before assigning to variable
            $filter = new PHPFrame_RegexpFilter(array(
                "regexp"     => '/^[a-zA-Z_]+$/',
                "min_length" => 1,
                "max_length" => 50
            ));

            $this->_array['action'] = $filter->process($str);
        }

        return $this->_array['action'];
    }

    /**
     * Get request/post array
     *
     * @return array
     * @since  1.0
     */
    public function params()
    {
        return $this->_array['params'];
    }

    /**
     * Get/set a request variable
     *
     * @param string $key   The param key.
     * @param mixed  $value [Optional]
     *
     * @return mixed
     * @since  1.0
     */
    public function param($key, $value=null)
    {
        $key = trim((string) $key);

        if (!is_null($value)) {
            $this->_array['params'][$key] = $value;
        }

        // Return null if index is not defined
        if (!isset($this->_array['params'][$key])) {
            return null;
        }

        return $this->_array['params'][$key];
    }

    /**
     * Get request headers
     *
     * @return array
     * @since  1.0
     */
    public function headers()
    {
        return $this->_array["headers"];
    }

    /**
     * Get/set a request header
     *
     * @param string $key   The header name.
     * @param string $value [Optional] The header value.
     *
     * @return string|null
     * @since  1.0
     */
    public function header($key, $value=null)
    {
        $key = trim((string) $key);

        if (!is_null($value)) {
            $value = trim((string) $value);
            $this->_array["headers"][$key] = $value;
        }

        if (!isset($this->_array["headers"][$key])) {
            return null;
        }

        return $this->_array["headers"][$key];
    }

    /**
     * Get request method. Either "GET", "POST" or "CLI".
     *
     * @param string $str [Optional] Allowed values are "GET", "POST" and "CLI".
     *
     * @return string
     * @since  1.0
     */
    public function method($str=null)
    {
        if (!is_null($str)) {
            // Filter value before assigning to variable
            $filter = new PHPFrame_RegexpFilter(array(
                "regexp"     => '/^(GET|POST|PUT|DELETE|HEAD|CLI)$/i',
                "min_length" => 3,
                "max_length" => 6
            ));

            $this->_array['method'] = $filter->process($str);
        }

        return $this->_array['method'];
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

    /**
     * Get/set file in the request.
     *
     * @param string $key   Key used to store file. Normally the name of the
     *                      form field to be used for posting the file.
     * @param array  $array [Optional] File data.
     *                      - name
     *                      - type
     *                      - tmp_name
     *                      - error
     *                      - size
     *
     * @return array|null
     * @since  1.0
     */
    public function file($key, array $array=null)
    {
        if (!is_null($array)) {
            $this->_array["files"][$key] = $array;
        }

        if (!isset($this->_array["files"][$key])) {
            return null;
        }

        return $this->_array["files"][$key];
    }

    /**
     * Get files attached to request
     *
     * @return array
     * @since  1.0
     */
    public function files()
    {
        return $this->_array["files"];
    }

    /**
     * Get/set remote address (IP)
     *
     * @param string $str [Optional] Requested IP address.
     *
     * @return string
     * @since  1.0
     */
    public function remoteAddr($str=null)
    {
        if (!is_null($str)) {
            $this->_array["remote_addr"] = (string) $str;
        }

        return $this->_array["remote_addr"];
    }

    /**
     * Get/set request URI
     *
     * @param string $str [Optional] Requested URI.
     *
     * @return string
     * @since  1.0
     */
    public function requestURI($str=null)
    {
        if (!is_null($str)) {
            $this->_array["request_uri"] = $str;
        }

        return $this->_array["request_uri"];
    }

    /**
     * Get/set request script name.
     *
     * @param string $str [Optional] The name of the requested script.
     *
     * @return string
     * @since  1.0
     */
    public function scriptName($str=null)
    {
        if (!is_null($str)) {
            $this->_array["script_name"] = (string) $str;
        }

        return $this->_array["script_name"];
    }

    /**
     * Get/set request query string.
     *
     * @param string $str [Optional] The query string.
     *
     * @return string
     * @since  1.0
     */
    public function queryString($str=null)
    {
        if (!is_null($str)) {
            $this->_array["query_string"] = $str;
        }

        return $this->_array["query_string"];
    }

    /**
     * Get/set the request time (unix timestamp).
     *
     * @param int $int [Optional] Unix timestamp.
     *
     * @return int
     * @since  1.0
     */
    public function requestTime($int=null)
    {
        if (!is_null($int)) {
            $this->_array["request_time"] = (int) $int;
        }

        return $this->_array["request_time"];
    }

    /**
     * Get/set output file absolute path. If not set no output will not be
     * written to file, which is the normal behaviour.
     *
     * @param string $str Absolute path for file to write output.
     *
     * @return string
     * @since  1.0
     */
    public function outfile($str=null)
    {
        if (!is_null($str)) {
            $this->_array["outfile"] = (string) $str;
        }

        return $this->_array["outfile"];
    }

    /**
     * Is Quiet request? (no output)
     *
     * @param bool $bool [Optional] TRUE or FALSE.
     *
     * @return bool
     * @since  1.0
     */
    public function quiet($bool=null)
    {
        if (!is_null($bool)) {
            $this->_array["quiet"] = (bool) $bool;
        }

        return $this->_array["quiet"];
    }

    /**
     * Is AJAX request?
     *
     * @param bool $bool [Optional] TRUE or FALSE.
     *
     * @return bool
     * @since  1.0
     */
    public function ajax($bool=null)
    {
        if (!is_null($bool)) {
            $this->_array["ajax"] = (bool) $bool;
        }

        return $this->_array["ajax"];
    }

    /**
     * Has request already been dispatched?
     *
     * @param bool $bool TRUE or FALSE.
     *
     * @return bool
     * @since  1.0
     */
    public function dispatched($bool=null)
    {
        if (!is_null($bool)) {
            $this->_dispatched = (bool) $bool;
        }

        return $this->_dispatched;
    }

    /**
     * Get/set request's raw body.
     *
     * @param string $str [Optional] The raw request body.
     *
     * @return string
     * @since  1.0
     */
    public function body($str=null)
    {
        if (!is_null($str)) {
            $this->_raw_body = (string) $str;
        }

        return $this->_raw_body;
    }
}
