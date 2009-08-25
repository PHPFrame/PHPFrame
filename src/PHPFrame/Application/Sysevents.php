<?php
/**
 * PHPFrame/Application/Sysevents.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Application
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * System Events Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Application
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Application_Sysevents
{
    /**
     * Events summary
     * 
     * @var array
     */
    private $_summary=array();
    /**
     * Events log
     * 
     * An array containig more info about what was reported to the system events.
     * 
     * @var array
     */
    private $_events_log=array();
    
    /**
     * Constructor
     * 
     * @return void
     * @since  1.0
     */
    public function __construct() {}
    
    /**
     * Set system events summary
     * 
     * @param string $msg  The summary message.
     * @param string $type Possible values "error", "warning", "notice", "success", 
     *                     "info". Default value is "error".
     * 
     * @return void
     * @since  1.0
     */
    public function setSummary($msg, $type=null) 
    {
        if (is_null($type)) $type = "error";
        $this->_summary = array($type, $msg);
    }
    
    /**
     * Add event log
     * 
     * @param string $msg  The event log message.
     * @param string $type Possible values "error", "warning", "notice", "success", 
     *                     "info". Default value is "error".
     * 
     * @return void
     * @since  1.0
     */
    public function addEventLog($msg, $type=null) 
    {
        if (is_null($type)) $type = "error";
        $this->_events_log[] = array($type, $msg);
    }
    
    /**
     * Get system events as array.
     * 
     * This method is used to get the system events for output.
     * 
     * @return array
     * @since  1.0
     */
    public function asArray() 
    {
        return array("summary" => $this->_summary, "events_log" => $this->_events_log);
    }
    
    /**
     * Get system events as string.
     * 
     * @return string
     * @since  1.0
     */
    public function asString() 
    {
        $str = "";
        if (count($this->_summary) > 0) {
            $str .= ucfirst($this->_summary[0]).": ".$this->_summary[1];
        }
        
        if (is_array($this->_events_log) && count($this->_events_log) > 0) {
            $str .= "\nEvents log: \n";
            foreach ($this->_events_log as $event_log) {
                $str .= ucfirst($event_log[0]).": ".$event_log[1]."\n";
            }
        }
        
        return $str;
    }
    
    /**
     * Clear system events from object and session.
     * 
     * This should be done after displaying the messages to the user.
     * 
     * @return void
     * @since  1.0
     */
    public function clear() 
    {
        // Clear private vars
        $this->_summary = array();
        $this->_events_log = array();
    }
}
