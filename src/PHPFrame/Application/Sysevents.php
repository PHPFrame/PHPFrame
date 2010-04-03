<?php
/**
 * PHPFrame/Application/Sysevents.php
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
 * System Events Class
 *
 * @category PHPFrame
 * @package  Application
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
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
    private $_status_code = 200;

    /**
     * Constructor
     *
     * @return void
     * @since  1.0
     */
    public function __construct()
    {
        $this->_events = array();
    }

    /**
     * Magic method invoked when Sysevents object is used as string
     *
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        $str = "";

        foreach ($this->_events as $event) {
            switch ($event[1]) {
            case PHPFrame_Subject::EVENT_TYPE_ERROR :
                $event_type = "Error";
                break;
            case PHPFrame_Subject::EVENT_TYPE_WARNING :
                $event_type = "Warning";
                break;
            case PHPFrame_Subject::EVENT_TYPE_NOTICE :
                $event_type = "Notice";
                break;
            case PHPFrame_Subject::EVENT_TYPE_INFO :
                $event_type = "Info";
                break;
            case PHPFrame_Subject::EVENT_TYPE_SUCCESS :
                $event_type = "Success";
                break;
            }

            $str .= strtoupper($event_type).": ".$event[0]."\n";
        }

        return $str;
    }

    /**
     * Get iterator object
     *
     * Note that we reverse the order of the elements in order to iterate starting
     * from the latest entry.
     *
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
     * @param string $msg  The message we want to append.
     * @param int    $type The event type. See constants in
     *                     {@link PHPFrame_Subject} class.
     *
     * @return void
     * @since  1.0
     */
    public function append($msg, $type=PHPFrame_Subject::EVENT_TYPE_INFO)
    {
        $this->_events[] = array($msg, $type);
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
        $this->_events = array();
        $this->statusCode(200);
    }

    /**
     * Implementation of update method triggered by observed objects
     *
     * @param PHPFrame_Subject $subject Reference to the observed subject.
     *
     * @return void
     * @see    PHPFrame_Observer::doUpdate()
     * @since  1.0
     */
    protected function doUpdate(PHPFrame_Subject $subject)
    {
        list($msg, $type) = $subject->getLastEvent();

        if (isset($msg) && !empty($msg)) {
            $this->append($msg, $type);
        }
    }

    /**
     * Get/set HTTP status code
     *
     * @param int $int [Optional]
     *
     * @return int
     * @since  1.0
     */
    public function statusCode($int=null)
    {
        if (!is_null($int)) {
            $this->_status_code = (int) $int;
        }

        return $this->_status_code;
    }
}
