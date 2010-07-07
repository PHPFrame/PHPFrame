<?php
/**
 * PHPFrame/HTTP/DownloadRequestListener.php
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
 * This class extends the HTTP_Request_Listener class provided by the PEAR/HTTP
 * package.
 *
 * @category PHPFrame
 * @package  HTTP
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @uses     Console_ProgressBar
 * @since    1.0
 */
class PHPFrame_DownloadRequestListener implements SplObserver
{
    /**
     * Handle for the target file
     *
     * @var int
     */
    private $_fp;
    /**
     * Console_ProgressBar intance used to display the indicator
     *
     * @var object
     */
    private $_bar;
    /**
     * Path to download directory
     *
     * @var string
     */
    private $_download_dir = null;
     /**
     * Name of the target file
     *
     * @var string
     */
    private $_filename = null;
    /**
     * Number of bytes received so far
     *
     * @var int
     */
    private $_size = 0;

    /**
     * Constructor
     *
     * @return void
     * @since  1.0
     */
    public function __construct()
    {
        $this->_download_dir = getcwd();
    }

    /**
     * Set target file name
     *
     * @param string $str Target file name
     *
     * @return void
     * @since  1.0
     */
    public function setFileName($str)
    {
        $this->_filename = (string) $str;
    }

    /**
     * Set path to download directory
     *
     * @param string $str Path to download directory
     *
     * @return void
     * @since  1.0
     */
    public function setDownloadDir($str)
    {
        $this->_download_dir = (string) $str;
    }

    /**
     * Handle update event updates
     *
     * @param SplSubject $subject Reference to observed subject.
     *
     * @return void
     * @since  1.0
     */
    public function update(SplSubject $subject)
    {
        $event = $subject->getLastEvent();
print_r($event["name"]);
echo "\n";
        switch ($event["name"]) {
        case "receivedHeaders" :
            $response = $event["data"];

            // If status is not OK we return (important for redirects)
            if ($response->getStatus() != 200) {
                return;
            }

            // Try to get file name from headers if not set yet
            if (!$this->_filename) {
                $content_disp = $response->getHeader("content-disposition");
                if (!empty($content_disp)) {
                    $pattern = "/filename=['\"]?(.*)['\"]?/";
                    preg_match($pattern, $content_disp, $matches);
                    if (isset($matches[1])) {
                        $this->setFileName($matches[1]);
                    }
                }
            }

            // If no target has been specified so far we use URLs file name
            if (empty($this->_filename)) {
                $url           = $subject->getURL();
                $this->_filename = end(explode("/", $url->getPath()));
                if (empty($this->_filename)) {
                    $this->_filename = $url->getHost();
                }
            }

            $this->_fp = @fopen($this->_download_dir.DS.$this->_filename, 'wb');
            if (!$this->_fp) {
                $msg  = "Cannot open '".$this->_download_dir.DS;
                $msg .= $this->_filename."'";
                throw new RuntimeException($msg);
            }

            $content_length = $response->getHeader("content-length");
            $this->_bar     = new Console_ProgressBar(
                '* '.$this->_filename.' %fraction% KB [%bar%] %percent%',
                '=>',
                '-',
                79,
                (
                isset($content_length)
                    ? ceil($content_length / 1024)
                    : 100
                )
            );

            $this->_size = 0;
            break;

        case "receivedBodyPart" :
        case "receivedEncodedBodyPart" :
            $this->_size += strlen($event["data"]);
            @$this->_bar->update(round($this->_size / 1024));
            fwrite($this->_fp, $event["data"]);
            break;

        case "receivedBody" :
            fclose($this->_fp);
            break;

        case "sentHeaders" :
        case "sentBodyPart" :
        case "connect" :
        case "disconnect" :
            break;

        default:
            $msg = "Unhandled event '".$event["name"]."'";
            throw new RuntimeException($msg);
        }
    }
}
