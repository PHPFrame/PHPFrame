<?php
/**
 * PHPFrame/Debug/Logger.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Debug
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * Logger Class
 *
 * This class implements the "Observer" base class in order to subscribe to updates
 * from "observable" objects (objects of type PHPFrame_Subject).
 *
 * @category PHPFrame
 * @package  Debug
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
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
     * @param string $file_name Absolute path to the log file.
     * @param int    $log_level [Optional] If log level is omitted all updates
     *                          issued by observed subjects will be written.
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
     * Get/set the log level.
     *
     * @param int $int [Optional] The log level. Possible values:
     *                 5 - success, info, notices, warnings and errors
     *                 4 - info, notices, warnings and errors
     *                 3 - notices, warnings and errors
     *                 2 - warnings and errors
     *                 1 - errors only
     *                 0 - Off
     *
     * @return int
     * @since  1.0
     */
    public function logLevel($int=null)
    {
        if (!is_null($int)) {
            if (!is_int($int) || $int < 0 || $int > 5) {
                $msg  = "Argument \$int passed to ".get_class($this)." must be ";
                $msg .= "an integer with a value between 0 and 5.";
                throw new InvalidArgumentException($msg);
            }

            $this->log_level = $int;
        }

        return $this->log_level;
    }

    /**
     * Handle updated issued by observed subjects.
     *
     * @param PHPFrame_Subject $subject Reference to observable object issuing
     *                                  the updates (observer pattern).
     *
     * @return void
     * @since  1.0
     * @see    PHPFrame_Observer::doUpdate()
     */
    protected function doUpdate(PHPFrame_Subject $subject)
    {
        list($msg, $type) = $subject->getLastEvent();

        if ($type <= $this->logLevel() || is_null($this->logLevel())) {
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
