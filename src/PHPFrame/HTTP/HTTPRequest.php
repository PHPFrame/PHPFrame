<?php
/**
 * PHPFrame/HTTP/HTTPRequest.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   HTTP
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * This class extends PEAR's HTTP_Request2 adding redirection code handling and 
 * download method. 
 * 
 * @category PHPFrame
 * @package  HTTP
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 * @see      HTTP_Request2
 */
class PHPFrame_HTTPRequest extends HTTP_Request2
{
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
     * Send HTTP request
     * 
     * @access public
     * @return HTTP_Request2_Response
     * @since  1.0
     */
    public function send()
    {
        $this->_response = parent::send();
        
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
