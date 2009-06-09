<?php
/**
 * @version       SVN: $Id$
 * @package       PHPFrame
 * @subpackage    utils
 * @copyright     2009 E-noise.com Limited
 * @license       http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * URI scheme Class
 * 
 * <pre>
 * foo://username:password@example.com:8042/over/there/?name=ferret#nose
 * \_/      \________________/\_________/ \__/\_________/  \_________/ \__/
 *  |           |               |        |     |             |       |
 *  |        userinfo       hostname    port  path        query   fragment
 *  |    \_______________________________/
 * scheme              authority
 * </pre>
 * 
 * @package        PHPFrame
 * @subpackage     utils
 * @since         1.0
 */
class PHPFrame_Utils_URI extends PHPFrame_Base_StdObject 
{
    /**
     * The URI scheme
     * 
     * ie: http, https, ftp, ...
     * 
     * @access    private
     * @var     string
     */
    private $_scheme=null;
    /**
     * The part of the URI string containing the user if any
     * 
     * @access    private
     * @var     string
     */
    private $_user=null;
    /**
     * The part of the URI string containing the user password if any
     * 
     * @access    private
     * @var     string
     */
    private $_pass=null;
    /**
     * The host name
     * 
     * @access    private
     * @var     string
     */
    private $_host=null;
    /**
     * Port number
     * 
     * @access    private
     * @var     int
     */
    private $_port=null;
    /**
     * The server directory
     * 
     * @access    private
     * @var     string
     */
    private $_dirname=null;
    /**
     * The sript name / file name without the extension
     * 
     * @access    private
     * @var     string
     */
    private $_filename=null;
    /**
     * The file extension
     * 
     * @access    private
     * @var     string
     */
    private $_extension=null;
    /**
     * An array containing the query string's name/value pairs.
     * 
     * @access    private
     * @var     array
     */
    private $_query=array();
    /**
     * The fragment part of the URI
     * 
     * @access    private
     * @var        string
     */
    private $_fragment=null;
    
    /**
     * Constructor
     * 
     * This method initialises the object by invoking parseURI(). 
     * If no URI is passed the current request's URI will be used.
     * 
     * If the app was invoked on the command line we don't try to detect and parse the current URL.
     * 
     * @access    public
     * @param    string    $uri The URI string.
     * @return     void
     * @since    1.0
     */
    public function __construct($uri='') 
    {
        if (empty($uri)) {
            $uri = $this->_getRequestURI();
        }
        
        $this->_parseURI($uri);
    }
    
    /**
     * Get the URI string from the current request
     * 
     * @access    private
     * @return     string    The current request's URL
     * @since    1.0
     */
    private function _getRequestURI() 
    {
        // Determine if the request was over SSL (HTTPS)
        if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) {
            $scheme = 'https';
        } 
        else {
            $scheme = 'http';
        }
        
        // If client is command line we use hardcoded value from config
        if (PHPFrame::getSession()->getClientName() == "cli") {
            return config::BASE_URL;
        }
        
        $uri = $scheme.'://'.$_SERVER['HTTP_HOST'];
        if (($scheme == 'http' && $_SERVER['SERVER_PORT'] != 80) || ($scheme == 'https' && $_SERVER['SERVER_PORT'] != 443)) {
            $uri .= ':'.$_SERVER['SERVER_PORT'];    
        }
        $uri .= $_SERVER["REQUEST_URI"];
        
        return $uri;
    }
    
    /**
     * Parse URI
     * 
     * This method parses the passed URI and sets the object's properties accordingly.
     * 
     * @access    private
     * @param    string    $uri The URI to parse
     * @return    void
     * @since    1.0
     */
    private function _parseURI($uri) 
    {
        // Parse URI using PHPs parse_url() method
        $array = parse_url($uri);
        
        $this->_scheme = $array['scheme'];
        $this->_host = $array['host'];
        
        if (array_key_exists('port', $array)) {
            $this->_port = $array['port'];    
        }
        elseif ($this->_scheme == 'http') {
            $this->_port = 80;
        }
        elseif ($this->_scheme == 'https') {
            $this->_port = 443;
        }
        
        if (array_key_exists('user', $array)) {
            $this->_user = $array['user'];
        }
        
        if (array_key_exists('pass', $array)) {
            $this->_pass = $array['pass'];
        }
        
        if (array_key_exists('fragment', $array)) {
            $this->_fragment = $array['fragment'];
        }
        
        // Parse path into components
        if (array_key_exists('path', $array)) {
            // If no file name is specified (path ends in forward slash)
            if (preg_match('/^(.*)\/$/', $array['path'], $matches)) {
                $this->_dirname = $matches[1];
            }
            else {
                $pathinfo = pathinfo($array['path']);
                if (array_key_exists('dirname', $pathinfo)) {
                    $this->_dirname = $pathinfo['dirname'];
                }
                if (array_key_exists('filename', $pathinfo)) {
                    $this->_filename = $pathinfo['filename'];
                }
                if (array_key_exists('extension', $pathinfo)) {
                    $this->_extension = $pathinfo['extension'];
                }    
            }
        }
        
        // Parse query string
        if (array_key_exists('query', $array)) {
            parse_str($array['query'], $this->_query);    
        }
    }
    
    /**
     * Get base URL
     * 
     * This method retrieves the base URL for the current state of the URI object.
     * 
     * @access    public
     * @return    string
     * @since    1.0
     */
    public function getBase() 
    {
        $base = $this->_scheme."://".$this->_host;
        if (($this->_scheme == "http" && $this->_port != 80)
             || ($this->_scheme == "https" && $this->_port != 443)) {
            $base .= ":".$this->_port;    
        }
        
        // Add dir name to base
        $base .= $this->_dirname;
        
        // Add trailing slash if needed
        if (!preg_match('/\/$/', $base)) {
            $base .= "/";
        }
        
        return $base;
    }
    
    /**
     * Print URI object as URI string
     * 
     * @access    public
     * @return     string
     * @since    1.0
     */
    public function __toString() 
    {
        $str = $this->getBase().$this->_filename.".".$this->_extension;
        if (is_array($this->_query) && count($this->_query) > 0) {
            $str .= "?";
            $i=0;
            foreach ($this->_query as $key=>$value) {
                if ($i>0) $str .= "&";
                $str .= $key."=".$value;
                $i++;
            }
        }
        if (!is_null($this->_fragment)) {
            $str .= "#".$this->_fragment;
        }
        
        return $str;
    }
}
