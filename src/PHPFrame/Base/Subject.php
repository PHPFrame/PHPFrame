<?php
/**
 * PHPFrame/Base/Subject.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Base
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 * @since     1.0
 */

/**
 * This class provides an abstract implementation of the SplSubject interface.
 *
 * This class is designed to work together with the {@link PHPFrame_Observer} class.
 *
 * @category PHPFrame
 * @package  Base
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @see      PHPFrame_Observer
 * @since    1.0
 */
abstract class PHPFrame_Subject implements SplSubject
{
    const EVENT_TYPE_ERROR   = 0x00000001;
    const EVENT_TYPE_WARNING = 0x00000002;
    const EVENT_TYPE_NOTICE  = 0x00000003;
    const EVENT_TYPE_INFO    = 0x00000004;
    const EVENT_TYPE_SUCCESS = 0x00000005;

    /**
     * An "storage" object to store observers.
     *
     * @var SplObjectStorage
     */
    private $_obs = null;
    /**
     * A simple array with the last raised event (error, success, info, ...).
     *
     * @var array
     */
    private $_last_event = array();

    /**
     * Get controller's success flag.
     *
     * @return boolean
     * @since  1.0
     */
    public function getLastEvent()
    {
        return $this->_last_event;
    }

    /**
     * Notify event to observers.
     *
     * @param string $msg  The event message.
     * @param int    $type The event type. See class constants.
     *
     * @return void
     * @since  1.0
     */
    public function notifyEvent($msg, $type=self::EVENT_TYPE_INFO)
    {
        $msg = trim((string) $msg);

        $valid_types = array(
            self::EVENT_TYPE_ERROR,
            self::EVENT_TYPE_INFO,
            self::EVENT_TYPE_NOTICE,
            self::EVENT_TYPE_SUCCESS,
            self::EVENT_TYPE_WARNING
        );

        if (!in_array($type, $valid_types)) {
            $exception_msg  = "Event type not valid. See class constants in ";
            $exception_msg .= get_class($this)." for allowed values.";
            throw new DomainException($exception_msg);
        }

        // Update last event
        $this->_last_event = array($msg, $type);

        // Notify observers
        $this->notify();
    }

    /**
     * Attach an observer to this subject.
     *
     * @param PHPFrame_Observer $observer The object to attach to this subject.
     *
     * @return void
     * @since  1.0
     */
    public function attach(SplObserver $observer)
    {
        if (!$this->_obs instanceof SplObjectStorage) {
            $this->_obs = new SplObjectStorage();
        }

        $this->_obs->attach($observer);
    }

    /**
     * Detach an object from the subject.
     *
     * @param SplObserver $observer The observer object to detach.
     *
     * @return void
     * @since  1.0
     */
    public function detach(SplObserver $observer)
    {
        $this->_obs->detach($observer);
    }

    /**
     * Notify observers.
     *
     * @return void
     * @since  1.0
     */
    public function notify()
    {
        if ($this->_obs instanceof SplObjectStorage) {
            foreach ($this->_obs as $obs) {
                $obs->update($this);
            }
        }
    }
}
