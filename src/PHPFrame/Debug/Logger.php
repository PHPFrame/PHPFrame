<?php
/**
 * PHPFrame/Debug/Logger.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Debug
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
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
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see      PHPFrame_Observer
 * @since    1.0
 */
abstract class PHPFrame_Logger extends PHPFrame_Observer 
    implements IteratorAggregate
{
	private $_file_name, $_log_level;
	
	/**
	 * Constructor
	 * 
	 * @param string $file_name
	 * @param int    $log_level
	 * 
	 * @access public
	 * @return void
	 * @since  1.0
	 */
    public function __construct($file_name, $log_level=0)
    {
    	if (!is_string($file_name)) {
    	    $msg  = "Argument \$file_name in ".get_class($this)."::";
    	    $msg .= __FUNCTION__."() must be of type 'string' and value of ";
    	    $msg .= "type '".gettype($file_name)."' was passed.";
    	    throw new InvalidArgumentException($msg);
    	}
    	
    	$this->_file_name = $file_name;
    	$this->_log_level = $log_level;
    }
    
    /**
     * Implementation of IteratorAggregate interface
     * 
     * @access public
     * @return Iterator
     * @since  1.0
     */
    abstract public function getIterator();
    
    /**
     * Handle updated issued by observed subjects
     * 
     * @access public
     * @return void
     * @since  1.0
     * @see    PHPFrame_Observer::doUpdate()
     */
    protected function doUpdate(PHPFrame_Subject $subject)
    {
        list($msg, $type) = $subject->getLastEvent();
        
        $log_level = PHPFrame::Config()->get("debug.log_level");
        if ($type <= $log_level) {
            self::write($msg);
        }
    }
    
    /**
     * Write to log
     * 
     * @param string $str
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    abstract public function write($str);
}
