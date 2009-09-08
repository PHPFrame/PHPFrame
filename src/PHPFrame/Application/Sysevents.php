<?php
/**
 * PHPFrame/Application/Sysevents.php
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
 * System Events Class
 * 
 * @category PHPFrame
 * @package  Application
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_Sysevents extends PHPFrame_Observer 
    implements IteratorAggregate, Countable
{
    /**
     * Internal array to store the system events
     * 
     * @var array
     */
    private $_events;
    
    /**
     * Constructor
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct()
    {
        $this->_events = array();
    }
    
    public function __toString()
    {
        $str = "";
        
        foreach ($this->_events as $event) {
            $str .= strtoupper($event[1]).": ".$event[0]."\n";
        }
        
        return $str;
    }
    
    /**
     * Get iterator object
     * 
     * Note that we reverse the order of the elements in order to iterate starting 
     * from the latest entry.
     * 
     * @access public
     * @return Iterator
     * @since  1.0
     */
    public function getIterator()
    {
        return new ArrayIterator(array_reverse($this->_events));
    }
    
    /**
     * Count elements in internal array
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function count()
    {
        return count($this->_events);
    }
    
    /**
     * Append a system event
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function append($msg, $type=self::INFO)
    {
        $this->_events[] = array($msg, $type);
    }
    
    /**
     * Clear system events from object and session.
     * 
     * This should be done after displaying the messages to the user.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function clear() 
    {
        // Clear private vars
        $this->_events = array();
    }
    
    /**
     * Implementation of update method triggered by observed objects
     * 
     * @see src/PHPFrame/Base/PHPFrame_Observer#doUpdate($subject)
     * 
     * @access protected
     * @return void
     * @since  1.0
     */
    protected function doUpdate(PHPFrame_Subject $subject)
    {
        list($msg, $type) = $subject->getLastEvent();
        
        if (isset($msg) && !empty($msg)) {
            $this->append($msg, $type);
        }
    }
}
