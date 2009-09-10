<?php
/**
 * PHPFrame/HTTP/DownloadRequestListener.php
 * 
 * PHP version 5
 * 
 * @category   PHPFrame
 * @package    HTTP
 * @subpackage Request
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * This class extends the HTTP_Request_Listener class provided by the PEAR/HTTP 
 * package.
 * 
 * @category   PHPFrame
 * @package    HTTP
 * @subpackage Request
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @uses       Console_ProgressBar
 * @since      1.0
 */
class PHPFrame_HTTP_DownloadRequestListener extends HTTP_Request_Listener
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
    * Name of the target file
    * 
    * @var string
    */
    private $_target;
   /**
    * Number of bytes received so far
    * 
    * @var int
    */
    private $_size = 0;
    
    /**
     * Constructor
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct()
    {
        parent::__construct();
    }

   /**
    * Opens the target file
    * @param string Target file name
    * 
    * @throws PEAR_Error
    * @access public
    * @return void
    * @since  1.0
    */
    public function setTarget($target)
    {
        $this->_target = $target;
        $this->_fp = @fopen($target, 'wb');
        if (!$this->_fp) {
            PEAR::raiseError("Cannot open '{$target}'");
        }
    }
    
    /**
     * Handle update event updates
     * 
     * @param $subject
     * @param $event
     * @param $data
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function update($subject, $event, $data=null)
    {
        switch ($event) {
            case 'gotHeaders':
                $this->_bar = new Console_ProgressBar(
                    '* '.$subject->_url->path.' %fraction% KB [%bar%] %percent%', 
                    '=>', 
                    '-', 
                    79, 
                    (isset($data['content-length']) ? 
                        round($data['content-length'] / 1024) : 100)
                );
                
                $this->_size = 0;
                break;

            case 'tick':
                $this->_size += strlen($data);
                $this->_bar->update(round($this->_size / 1024));
                fwrite($this->_fp, $data);
                break;

            case 'gotBody':
                fclose($this->_fp);
                break;

            case 'sentRequest': 
            case 'connect':
            case 'disconnect':
                break;

            default:
                PEAR::raiseError("Unhandled event '{$event}'");
        }
    }
}
