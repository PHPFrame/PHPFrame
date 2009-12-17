<?php
/**
 * PHPFrame/Debug/Logger.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Debug
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * Logger Class
 * 
 * This class implements the "Observer" base class in order to subscribe to updates
 * from "observable" objects (objects of type PHPFrame_Subject).
 * 
 * @category PHPFrame
 * @package  Debug
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @see      PHPFrame_Observer
 * @since    1.0
 */
abstract class PHPFrame_Logger extends PHPFrame_Observer 
    implements IteratorAggregate
{
    protected $file_name, $log_level;
    
    /**
     * Constructor
     * 
     * @param string $file_name
     * @param int    $log_level [Optional] If log level is omitted all updates
     *                                     issued by observed subjects will be
     *                                     written.
     * 
     * @return void
     * @since  1.0
     */
    public function __construct($file_name, $log_level=null)
    {
        if (!is_string($file_name)) {
            $msg  = "Argument \$file_name in ".get_class($this)."::";
            $msg .= __FUNCTION__."() must be of type 'string' and value of ";
            $msg .= "type '".gettype($file_name)."' was passed.";
            throw new InvalidArgumentException($msg);
        }
        
        $this->file_name = $file_name;
        $this->log_level = (int) $log_level;
    }
    
    /**
     * Handle updated issued by observed subjects
     * 
     * @return void
     * @since  1.0
     * @see    PHPFrame_Observer::doUpdate()
     */
    protected function doUpdate(PHPFrame_Subject $subject)
    {
        list($msg, $type) = $subject->getLastEvent();
        
        if ($type <= $this->log_level || is_null($this->log_level)) {
            $this->write($msg);
        }
    }
    
    /**
     * Write to log
     * 
     * @param string|array $msg The string to append to log file
     * 
     * @return void
     * @since  1.0
     */
    abstract public function write($msg);
}
