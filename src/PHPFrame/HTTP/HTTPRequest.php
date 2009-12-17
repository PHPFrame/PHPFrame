<?php
/**
 * PHPFrame/HTTP/HTTPRequest.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   HTTP
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * This class extends PEAR's HTTP_Request2 adding redirection code handling and 
 * download method. 
 * 
 * @category PHPFrame
 * @package  HTTP
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since    1.0
 * @see      HTTP_Request2
 */
class PHPFrame_HTTPRequest extends HTTP_Request2
{
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
     * 
     * @param string|Net_Url2 $url    [Optional]
     * @param string          $method [Optional]
     * @param array           $config [Optional]
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct(
        $url=null, 
        $method=HTTP_Request2::METHOD_GET, 
        array $config=array()
    )
    {
        parent::__construct($url, $method, $config);
    }
    
    /**
     * Set cache time. Set to 0 to disable.
     * 
     * @param int $int The cache time in seconds.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setCacheTime($int)
    {
        $this->_cache = (int) $int;
    }
    
    /**
     * Get cache time in seconds. 0 means off.
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function getCacheTime()
    {
        return $this->_cache;
    }
    
    /**
     * Set cache directory.
     * 
     * @param string $str Full path to the cache directory.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setCacheDir($str)
    {
        $this->_cache_dir = (string) $str;
        
        // Make sure the directory is writable
        PHPFrame_Filesystem::ensureWritableDir($this->_cache_dir);
    }
    
    /**
     * Get cache directory
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getCacheDir()
    {
        // Try to use default cache dir if not set
        if (is_null($this->_cache_dir)) {
            $msg  = get_class($this)." could not determine the path to the";
            $msg .= " cache directory";
            throw new RuntimeException($msg);
        }
        
        return $this->_cache_dir;
    }
    
    /**
     * Send HTTP request
     * 
     * @access public
     * @return HTTP_Request2_Response
     * @since  1.0
     */
    public function send()
    {
        // If cache is turned on
        if ($this->getCacheTime() > 0) {
            $cache_file_name  = $this->getCacheDir();
            $cache_file_name .= DS.md5($this->getUrl()->getURL());
            if (is_file($cache_file_name)) {
                $cache_file = new SplFileObject($cache_file_name, "r+");
                if ((time() - $cache_file->getMTime()) < $this->getCacheTime()) {
                    // Fetch data from cache file
                    $lines           = iterator_to_array($cache_file);
                    $contents        = implode("\n", $lines);
                    $serialised      = base64_decode($contents);
                    $this->_response = unserialize($serialised);
                }
            } else {
                $cache_file = new SplFileObject($cache_file_name, "w");
            }
        }
        
        // If no response has been loaded from cache we get the it via HTTP
        if (!$this->_response instanceof HTTP_Request2_Response) {
            $this->_response = parent::send();
            
            // If cache is turned on we store the fetched data
            if ($this->getCacheTime() > 0) {
                $cache_file->rewind();
                $cache_file->fwrite(base64_encode(serialize($this->_response)));
            }
        }
        
        // If we get a redirect status we send a new request
        $response_status = $this->_response->getStatus();
        $redirect_codes  = array(301, 302);
        if (in_array($response_status, $redirect_codes)) {
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
                
                $this->setURL($url);
                
                // Call send() again with the redirect URL
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
     * @access public
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
