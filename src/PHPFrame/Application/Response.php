<?php
/**
 * PHPFrame/Application/Response.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Application
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * This class encapsulates an HTTP response
 * 
 * @category PHPFrame
 * @package  Application
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_Response
{
    const STATUS_OK                    = 200;
    const STATUS_MOVED_PERMANENTLY     = 301;
    const STATUS_FOUND                 = 302;
    const STATUS_SEE_OTHER             = 303;
    const STATUS_BAD_REQUEST           = 400;
    const STATUS_UNAUTHORIZED          = 401;
    const STATUS_FORBIDDEN             = 403;
    const STATUS_NOT_FOUND             = 404;
    const STATUS_INTERNAL_SERVER_ERROR = 500;
    const STATUS_NOT_IMPLEMENTED       = 501;
    
    /**
     * Instance of itself
     * 
     * @var PHPFrame_Response
     */
    private static $_instance = null;
    /**
     * HTTP Response status code
     * 
     * @var int
     */
    private $_code = self::STATUS_OK;
    /**
     * An array containing the raw headers
     * 
     * @var array
     */
    private $_headers = array(
        "X-Powered-By"=>null,
        "Expires"=>null,
        "Cache-Control"=>null,
        "Pragma"=>null,
        "Status"=>null,
        "Content-Language"=>null,
        "Content-Type"=>null
    );
    /**
     * The document object used to render response
     * 
     * @var PHPFrame_Document
     */
    private $_document = null;
    /**
     * A pathway object for this view
     * 
     * @var PHPFrame_Pathway
     */
    private $_pathway = null;
    
    /**
     * Constructor
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function __construct()
    {
        foreach (headers_list() as $header) {
            if (preg_match('/^([a-zA-Z-]+):(.*)$/', $header, $matches)) {
                $this->setHeader($matches[1], trim($matches[2]));
            }
        }
        
        $this->setStatusCode(200);
        
        $x_powered_by  = $this->getHeader("X-Powered-By");
        $x_powered_by .= " PHPFrame/".PHPFrame::RELEASE_VERSION;
        $this->setHeader("X-Powered-By", $x_powered_by);
        
        $config = PHPFrame::Config();
        $this->setHeader("Content-Language", $config->get("default_lang"));
        
        // Acquire pathway object
        $this->_pathway = new PHPFrame_Pathway();
    }
    
    /**
     * Convert object to string
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        $str = "";
        
        foreach ($this->getHeaders() as $key=>$value) {
            $str .= ucwords($key).": ".$value."\n";
        }
        
        $str .= "\n".$this->getBody();
        
        return $str;
    }
    
    /**
     * Get instance
     * 
     * @static
     * @access public
     * @return PHPFrame_Response
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
     * Set the HTTP response status code
     * 
     * @param int $int The status code
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setStatusCode($int)
    {
        $array = array(200, 301, 302, 303, 400, 401, 403, 404, 500, 501);
        
        if (!in_array($int, $array)) {
            $msg  = "HTTP response status code not valid. Valid codes are: ";
            $msg .= "'".implode("','", $array)."'.";
            throw new InvalidArgumentException($msg);
        }
        
        $this->_code = $int;
        
        $this->setHeader("Status", $this->_code);
    }
    
    public function getHeaders()
    {
        return $this->_headers;
    }
    
    public function getHeader($key)
    {
        if (!isset($this->_headers[$key])) {
            return null;
        }
        
        return $this->_headers[$key];
    }
    
    /**
     * Set header line
     * 
     * @param string $key   The header key
     * @param string $value The header value
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setHeader($key, $value) 
    {
        if (headers_sent()) {
            $msg = "Cannot modify header information - headers already sent";
            throw new LogicException($msg);
        }
        
        $this->_headers[$key] = $value;
    }
    
    public function getBody()
    {
        return (string) $this->getDocument();
    }
    
    public function appendBody($str)
    {
        
    }
    
    /**
     * Get the document object used as the response body
     * 
     * @access public
     * @return PHPFrame_Document
     * @since  1.0
     */
    public function getDocument()
    {
        return $this->_document;
    }
    
    /**
     * Set the document object used as the response body
     * 
     * @param PHPFrame_Document $document
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setDocument(PHPFrame_Document $document) 
    {
        $this->_document = $document;
        
        $this->setHeader("Content-Type", $this->_document->getMimeType());
    }
    
    /**
     * Get the view's pathway object
     * 
     * @access public
     * @return PHPFrame_Pathway
     * @since  1.0
     */
    public function getPathway()
    {
        return $this->_pathway;
    }
     
    /**
     * Send HTTP response to client
     *                                       
     * @access public
     * @return void
     * @since  1.0
     */
    public function send() 
    {
        // Send headers
        if (!headers_sent()) {
            foreach ($this->_headers as $line) {
                header($line);
            }
        }
        
        // Print response content (the document object)
        echo $this->getBody();
    }
}
