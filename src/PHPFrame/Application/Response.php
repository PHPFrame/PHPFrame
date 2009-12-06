<?php
/**
 * PHPFrame/Application/Response.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Application
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * This class encapsulates an HTTP response.
 * 
 * Response objects will use a "document" object descending from the abstract 
 * {@link PHPFrame_Document} class in order to store data that will be sent as 
 * a response. This document can be of different types: HTML, Plain, XML, ...
 * 
 * A "renderer" object of type {@link PHPFrame_IRenderer} is used to render 
 * the response body.
 * 
 * @category PHPFrame
 * @package  Application
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @uses     PHPFrame_Document, PHPFrame_IRenderer, PHPFrame_Pathway
 * @since    1.0
 */
class PHPFrame_Response
{
    /*
     * HTTP response status codes.
     */
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
     * HTTP Response status code.
     * 
     * @var int
     */
    private $_code = self::STATUS_OK;
    /**
     * An array containing the raw headers.
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
     * The document object used to render response.
     * 
     * @var PHPFrame_Document
     */
    private $_document = null;
    /**
     * Instance of PHPFrame_IRenderer used to render.
     * 
     * @var PHPFrame_IRenderer
     */
    private $_renderer = null;
    /**
     * A pathway object for this view.
     * 
     * @var PHPFrame_Pathway
     */
    private $_pathway = null;
    
    /**
     * Both arguments in the constructor are optional.
     * 
     * If no "document" object is passed an object type PHPFrame_PlainDocument 
     * will be used by default.
     * 
     * If no "renderer" object is passed an object type PHPFrame_PlainRenderer 
     * will be used by default.
     * 
     * @param PHPFrame_Document  $document [Optional] Document object used to 
     *                                     display the response.
     * @param PHPFrame_IRenderer $renderer [Optional] Renderer object used to 
     *                                     render the response.
     * 
     * @return void
     * @since  1.0
     */
    public function __construct(
        PHPFrame_Document $document=null, 
        PHPFrame_IRenderer $renderer=null
    ) {
        // Set document object
        if (is_null($document)) {
            $this->_document = new PHPFrame_PlainDocument();
        } else {
            $this->_document = $document;
        }
        
        // Set renderer object
        if (is_null($renderer)) {
            $this->_renderer = new PHPFrame_PlainRenderer();
        } else {
            $this->_renderer = $renderer;
        }
        
        // Get global headers
        foreach (headers_list() as $header) {
            if (preg_match('/^([a-zA-Z-]+):(.*)$/', $header, $matches)) {
                $this->setHeader($matches[1], trim($matches[2]));
            }
        }
        
        $this->setStatusCode(200);
        
        $x_powered_by  = $this->getHeader("X-Powered-By");
        $x_powered_by .= " PHPFrame/".PHPFrame::RELEASE_VERSION;
        $this->setHeader("X-Powered-By", $x_powered_by);
        
        // Acquire pathway object
        $this->_pathway = new PHPFrame_Pathway();
    }
    
    /**
     * Convert object to string
     * 
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        $str = "";
        
        foreach ($this->getHeaders() as $key=>$value) {
            $str .= ucwords($key).": ".$value."\n";
        }
        
        $str .= "\n".$this->getDocument();
        
        return $str;
    }
    
    /**
     * Set the HTTP response status code
     * 
     * @param int $int The status code. Allowed values are: 200, 301, 302, 303, 
     *                 400, 401, 403, 404, 500 and 501
     * 
     * @return void
     * @throws InvalidArgumentException if supplied code is not allowed.
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
    
    /**
     * Get HTTP headers as an associative array
     * 
     * @return array
     * @since  1.0
     */
    public function getHeaders()
    {
        return $this->_headers;
    }
    
    /**
     * Get a given HTTP header by key
     * 
     * @param string $key The name of the HTTP header we want to get, "Status" 
     *                    for example.
     * 
     * @return string
     * @since  1.0
     */
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
     * @return void
     * @since  1.0
     */
    public function setHeader($key, $value) 
    {
        $key   = trim((string) $key);
        $value = trim((string) $value);
        
        $this->_headers[$key] = $value;
    }
    
    /**
     * Get the document object used as the response body
     * 
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
     * @param PHPFrame_Document $document Document object used to display the 
     *                                    response.
     * 
     * @return void
     * @since  1.0
     */
    public function setDocument(PHPFrame_Document $document) 
    {
        $this->_document = $document;
        
        $this->setHeader("Content-Type", $this->_document->getMimeType());
    }
    
    /**
     * Get the renderer object
     * 
     * @return PHPFrame_IRenderer
     * @since  1.0
     */
    public function getRenderer()
    {
        return $this->_renderer;
    }
    
    /**
     * Set the renderer object
     * 
     * @param PHPFrame_IRenderer $renderer Renderer object used to display the 
     *                                     response.
     * 
     * @return void
     * @since  1.0
     */
    public function setRenderer(PHPFrame_IRenderer $renderer) 
    {
        $this->_renderer = $renderer;
    }
    
    /**
     * Get the view's pathway object
     * 
     * @return PHPFrame_Pathway
     * @since  1.0
     */
    public function getPathway()
    {
        return $this->_pathway;
    }
    
    /**
     * Set the response body. The passed value will be rendered using the 
     * response's renderer object.
     * 
     * @param mixed $value
     * @param bool  $render [Optional] Default value is TRUE. To bypass 
     *                                 renderer set to FALSE.
     * @param bool  $append [Optional] Default value is FALSE.
     * 
     * @return void
     * @since  1.0
     */
    public function setBody($value, $render=true, $append=false)
    {
    	// Render the value to append
    	if ($render) {
    	    $value = $this->getRenderer()->render($value);
    	}
    	
    	// Set the value in the document body
    	if ($append) {
    	    $this->getDocument()->appendBody($value);
    	} else {
    	    $this->getDocument()->setBody($value);
    	}
    }
     
    /**
     * Send HTTP response to client
     * 
     * @return void
     * @since  1.0
     */
    public function send() 
    {
        // Send headers
        if (!headers_sent()) {
            header("HTTP/1.1 ".$this->_code);
            
            foreach ($this->_headers as $key=>$value) {
                header($key.": ".$value);
            }
        }
        
        // Print response content (the document object)
        echo (string) $this->getDocument();
    }
}
