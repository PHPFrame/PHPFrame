<?php
/**
 * PHPFrame/HTTP/HTTPRequest.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   HTTP
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * This class extends PEAR's HTTP_Request2 adding redirection code handling and
 * download method.
 *
 * @category PHPFrame
 * @package  HTTP
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @see      HTTP_Request2
 * @since    1.0
 */
class PHPFrame_HTTPRequest extends HTTP_Request2
{
    /**
     * A boolean flag indicating whether 30x redirects should be automatically
     * followed or not. Default value is TRUE.
     *
     * @var bool
     */
    private $_handle_redirects = true;
    /**
     * Response object
     *
     * @var HTTP_Request2_Response
     */
    private $_response;
    /**
     * Cache time in seconds
     *
     * @var int
     */
    private $_cache = 0;
    /**
     * Full path to cache directory
     *
     * @var string
     */
    private $_cache_dir = null;

    /**
     * Constructor.
     *
     * @param string|Net_Url2 $url    [Optional]
     * @param string          $method [Optional]
     * @param array           $config [Optional]
     *
     * @return void
     * @since  1.0
     */
    public function __construct(
        $url=null,
        $method=HTTP_Request2::METHOD_GET,
        array $config=array()
    ) {
        parent::__construct($url, $method, $config);
    }

    public function __toString()
    {
        $config = $this->getConfig();

        $str  = $this->getMethod()." ".$this->getUrl()->getPath()." ";
        $str .= "HTTP/".$config["protocol_version"]."\n";
        $str .= "Host: ".$this->getUrl()->getHost()."\n";

        foreach ($this->getHeaders() as $key=>$value) {
            $str .= $key.": ".$value."\n";
        }

        $str .= "\n".$this->getBody()."\n";

        return $str;
    }

    /**
     * Get/set handle_redirects flag. This flag indicates whether 30x redirects
     * should be automatically followed or not. Default value is TRUE.
     *
     * @param bool $bool [Optional]
     *
     * @return bool
     * @since  1.0
     */
    public function handleRedirects($bool=null)
    {
        if (!is_null($bool)) {
            $this->_handle_redirects = (bool) $bool;
        }

        return $this->_handle_redirects;
    }

    /**
     * Get/set cache time. Set to 0 to disable.
     *
     * @param int $int [Optional] The cache time in seconds.
     *
     * @return int
     * @since  1.0
     */
    public function cacheTime($int=null)
    {
        if (!is_null($int)) {
            $this->_cache = (int) $int;
        }

        return $this->_cache;
    }

    /**
     * Get/set cache directory. If no cache directory is explicitly set the
     * current working directory will be used (given that a cache time has been
     * set using {@link PHPFrame_HTTPRequest::cacheTime()}).
     *
     * @param string $str [Optional] Full path to the cache directory.
     *
     * @return string|null
     * @since  1.0
     */
    public function cacheDir($str=null)
    {
        if (!is_null($str)) {
            $str = trim((string) $str);

            if (!is_dir($str) || !is_writable($str)) {
                $msg  = "Could not set the cache directory for HTTP requests. ";
                $msg .= "Directory '".$str."' doesn't exist or is not writable.";
                throw new RuntimeException($msg);
            }

            $this->_cache_dir = $str;
        }

        return $this->_cache_dir;
    }

    /**
     * Send HTTP request
     *
     * @return HTTP_Request2_Response
     * @since  1.0
     */
    public function send()
    {
        // If cache is turned on
        if ($this->cacheTime() > 0) {
            $cache_file_name  = $this->cacheDir();
            if (is_null($cache_file_name)) {
                $cache_file_name = getcwd();
            }

            $cache_file_name .= DS.md5($this->getUrl()->getURL());
            if (is_file($cache_file_name)) {
                $cache_file = new SplFileObject($cache_file_name, "r+");
                if ((time() - $cache_file->getMTime()) < $this->cacheTime()) {
                    // Fetch data from cache file
                    $lines           = iterator_to_array($cache_file);
                    $contents        = implode("\n", $lines);
                    $serialised      = base64_decode($contents);
                    $this->_response = @unserialize($serialised);
                }
            } else {
                $cache_file = new SplFileObject($cache_file_name, "w");
            }
        }

        // If no response has been loaded from cache we get the it via HTTP
        if (!$this->_response instanceof HTTP_Request2_Response) {
            try {
                $this->_response = parent::send();

            } catch (HTTP_Request2_Exception $e) {
                $msg = $e->getMessage();
                if (!$msg) {
                    $msg  = "An error occurred while sending HTTP request to '";
                    $msg .= $this->getUrl()."'.";
                }

                throw new RuntimeException($msg);
            }

            // If cache is turned on we store the fetched data
            if ($this->cacheTime() > 0) {
                $cache_file->rewind();
                $cache_file->fwrite(base64_encode(serialize($this->_response)));
            }
        }

        // If we get a redirect status we send a new request
        $response_status = $this->_response->getStatus();
        $redirect_codes  = array(301, 302);
        if ($this->handleRedirects()
            && in_array($response_status, $redirect_codes)
        ) {
            $header = $this->_response->getHeader();
            if (isset($header["location"])) {
                $url = "";
                // If location in header is relative we make it absolute
                if (!preg_match('/^(http|https|ftp):/', $header["location"])) {
                    $url_obj = $this->getUrl();
                    $url    .= $url_obj->getScheme()."://";
                    $url    .= $url_obj->getHost()."/";
                    $url    .= $url_obj->getPath()."/";
                }
                $url .= $header["location"];

                // Unset the response object and send() with redirect URL
                $this->_response = null;
                $this->setURL($url);
                return $this->send();
            }
        }

        return $this->_response;
    }

    /**
     * Send HTTP request and download file
     *
     * @param string $download_dir [Optional]
     * @param string $filename     [Optional]
     *
     * @return HTTP_Request2_Response
     * @since  1.0
     */
    public function download($download_dir=null, $filename=null)
    {
        $this->setConfig("store_body", false);

        $download_listener = new PHPFrame_DownloadRequestListener();
        if (!is_null($download_dir)) {
            $download_listener->setDownloadDir($download_dir);
        }
        if (!is_null($filename)) {
            $download_listener->setFileName($filename);
        }

        $this->attach($download_listener);

        return $this->send();
    }
}
