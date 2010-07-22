<?php
/**
 * PHPFrame/Application/Response.php
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
 * This class encapsulates an HTTP response.
 *
 * Response objects will use a "document" object descending from the abstract
 * {@link PHPFrame_Document} class in order to store data that will be sent as
 * a response. This document can be of different types: HTML, Plain, XML, ...
 *
 * A "renderer" object of type {@link PHPFrame_Renderer} is used to render
 * the response body.
 *
 * @category PHPFrame
 * @package  Application
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @uses     PHPFrame_Document, PHPFrame_Renderer
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
     * Instance of PHPFrame_Renderer used to render.
     *
     * @var PHPFrame_Renderer
     */
    private $_renderer = null;

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
     * @param PHPFrame_Renderer $renderer [Optional] Renderer object used to
     *                                     render the response.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(
        PHPFrame_Document $document=null,
        PHPFrame_Renderer $renderer=null
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
                $this->header($matches[1], trim($matches[2]));
            }
        }

        $this->statusCode(200);

        $x_powered_by  = $this->header("X-Powered-By");
        $x_powered_by .= " PHPFrame/".PHPFrame::RELEASE_VERSION;
        $this->header("X-Powered-By", $x_powered_by);
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

        foreach ($this->headers() as $key=>$value) {
            if ($value) {
                $str .= ucwords($key).": ".$value."\n";
            }
        }

        $status_code = $this->statusCode();
        if ($status_code < 300 || $status_code > 399) {
            $str .= "\n".$this->document();
        }

        return $str;
    }

    /**
     * Get/set the HTTP response status code.
     *
     * @param int $int [Optional] The status code. Allowed values are: 200,
     *                 301, 302, 303, 400, 401, 403, 404, 500 and 501
     *
     * @return int The current HTTP status code.
     * @throws InvalidArgumentException if supplied code is not allowed.
     * @since  1.0
     */
    public function statusCode($int=null)
    {
        if (!is_null($int)) {
            $array = array(200, 301, 302, 303, 400, 401, 403, 404, 500, 501);

            if (!in_array($int, $array)) {
                $msg  = "HTTP response status code not valid. Valid codes ";
                $msg .= "are: '".implode("','", $array)."'.";
                throw new InvalidArgumentException($msg);
            }

            $this->_code = $int;

            $this->header("Status", $this->_code);
        }

        return $this->_code;
    }

    /**
     * Get HTTP headers as an associative array
     *
     * @return array
     * @since  1.0
     */
    public function headers()
    {
        return $this->_headers;
    }

    /**
     * Get/set a given HTTP header line by key.
     *
     * @param string $key   The name of the HTTP header, "Status" for example.
     * @param string $value [Optional] The header value if we want to set it.
     *
     * @return string
     * @since  1.0
     */
    public function header($key, $value=null)
    {
        $key = trim((string) $key);

        if (is_null($value) && !isset($this->_headers[$key])) {
            return null;
        }

        if (!is_null($value)) {
            $this->_headers[$key] = trim((string) $value);
        }

        return $this->_headers[$key];
    }

    /**
     * Get/set the document object used as the response body.
     *
     * @param PHPFrame_Document $document [Optional] Document object used to
     *                                    display the response.
     *
     * @return PHPFrame_Document
     * @since  1.0
     */
    public function document(PHPFrame_Document $document=null)
    {
        if (!is_null($document)) {
            $this->_document = $document;

            $this->header("Content-Type", $this->_document->mime());
        }

        return $this->_document;
    }

    /**
     * Get/set the renderer object.
     *
     * @param PHPFrame_Renderer $renderer [Optional] Renderer object used to
     *                                     display the response.
     *
     * @return PHPFrame_Renderer
     * @since  1.0
     */
    public function renderer(PHPFrame_Renderer $renderer=null)
    {
        if (!is_null($renderer)) {
            $this->_renderer = $renderer;
        }

        return $this->_renderer;
    }

    /**
     * Get the response body (stored in document object).
     *
     * If a value is passed it will set the response body. The passed value
     * will be rendered using the response's renderer object.
     *
     * @param mixed $value  [Optional] The value to set as the response body.
     * @param bool  $render [Optional] Default value is TRUE. To bypass
     *                      renderer set to FALSE.
     * @param bool  $append [Optional] Default value is FALSE.
     *
     * @return string
     * @since  1.0
     */
    public function body($value=null, $render=true, $append=false)
    {
        if (!is_null($value)) {
            // Render the value to append
            if ($render) {
                $value = $this->renderer()->render($value);
            }

            // Set the value in the document body
            if ($append) {
                $this->document()->appendBody($value);
            } else {
                $this->document()->body($value);
            }
        }

        return $this->document()->body();
    }

    /**
     * Get the response title (stored in document object)
     *
     * @param string $str The string to set as document title.
     *
     * @return string
     * @since  1.0
     */
    public function title($str=null)
    {
        if (!is_null($str)) {
            $this->document()->title($str);
        }

        return $this->document()->title();
    }

    /**
     * Send HTTP response to client
     *
     * @return void
     * @since  1.0
     */
    public function send()
    {
        $status_code = $this->statusCode();

        // Send headers
        if (!headers_sent()) {
            header("HTTP/1.1 ".$status_code);

            foreach ($this->_headers as $key=>$value) {
                if ($value) {
                    header($key.": ".$value);
                }
            }
        }

        // Print response content (the document object)
        if ($status_code < 300 || $status_code > 399) {
            echo trim((string) $this->document());
        }
    }
}
