<?php
/**
 * PHPFrame/Exception/ExceptionHandler.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Exception
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * Exception and Error Handler Class
 *
 * @category PHPFrame
 * @package  Exception
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_ExceptionHandler extends PHPFrame_Subject
{
    /**
     * Propery holding single instance of this class
     *
     * @var PHPFrame_ExceptionHandler
     */
    private static $_instance = null;
    /**
     * Should exceptions be displayed?
     *
     * @var bool
     */
    private $_display_exceptions = true;
    /**
     * Boolean indicating whether catchable errors should be caught and
     * rethrown as exceptions.
     *
     * @var bool
     */
    private $_handle_errors = false;
    /**
     * Reference to last uncaught exception handled by this class.
     *
     * @var Exception
     */
    private $_last_exception;

    /**
     * We declare a private constructor to avoid instantiation from client code
     * enforce the singleton pattern.
     *
     * The constructor initialises the error and exception handlers
     *
     * This method encapsulates set_error_handler() and set_exception_handler().
     *
     * @return void
     * @since  1.0
     */
    private function __construct()
    {
        set_exception_handler(
            array("PHPFrame_ExceptionHandler", "handleException")
        );
    }

    /**
     * Get singleton instance of PHPFrame_ExceptionHandler
     *
     * @return PHPFrame_ExceptionHandler
     * @since  1.0
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * Restore error and exception handlers to PHP defaults
     *
     * This method encapsulates restore_error_handler() and
     * restore_exception_handler().
     *
     * @return void
     * @since  1.0
     */
    public static function restore()
    {
        restore_error_handler();
        restore_exception_handler();
    }

    /**
     * Get/set whether catchable errors should be caught and rethrown as
     * exceptions.
     *
     * @param bool $bool [Optional]
     *
     * @return bool
     * @since  1.0
     */
    public function catchableErrorsToExceptions($bool=null)
    {
        if (!is_null($bool)) {
            $this->_handle_errors = (bool) $bool;
            if ($this->_handle_errors) {
                set_error_handler(
                    array("PHPFrame_ExceptionHandler", "handleError")
                );
            } else {
                restore_error_handler();
            }
        }

        return $this->_handle_errors;
    }

    /**
     * Error handler method
     *
     * Handles PHP errors and converts them to exceptions.
     *
     * @param int    $errno      The level of the error raised, as an integer.
     * @param string $errstr     The error message, as a string.
     * @param string $errfile    The filename that the error was raised in.
     * @param int    $errline    The line number the error was raised at.
     * @param string $errcontext An array that points to the active symbol table
     *                           at the point the error occurred. In other
     *                           words, errcontext  will contain an array of
     *                           every variable that existed in the scope the
     *                           error was triggered in.
     *
     * @return void
     * @since  1.0
     */
    public static function handleError(
        $errno,
        $errstr,
        $errfile,
        $errline,
        $errcontext
    ) {
        // Throw error as custom exception
        throw new PHPFrame_ErrorException(
            $errstr,
            $errno,
            $errfile,
            $errline,
            $errcontext
        );
    }

    /**
     * Exceptions handler for uncaught exceptions.
     *
     * @param Exception $exception The uncaught exception.
     *
     * @return void
     * @since  1.0
     */
    public static function handleException(Exception $exception)
    {
        self::instance()->lastException($exception);

        // Notify event to observers
        self::instance()->notify();

        // Display the exception details if applicable
        if (self::instance()->_display_exceptions) {
            $response = new PHPFrame_Response();
            $response->document(new PHPFrame_PlainDocument());

            $status_code = $exception->getCode();
            if (!empty($status_code) && $status_code > 0) {
                $response->statusCode($status_code);
            } else {
                $response->statusCode(500);
            }

            $response->body($str, false);
            $response->send();
        }

        // Exit with error status
        exit(1);
    }

    /**
     * Get/set boolean to indicate whether or not exceptions should be shown.
     *
     * @param bool $bool [Optional] Boolean indicating whether exceptions
     *                   should be displayed.
     *
     * @return bool
     * @since  1.0
     */
    public static function displayExceptions($bool=null)
    {
        if (!is_null($bool)) {
            self::instance()->_display_exceptions = (bool) $bool;
        }

        return self::instance()->_display_exceptions;
    }

    /**
     * Get/set last exception handled by singleton instance.
     *
     * @param Exception $e [Optional]
     *
     * @return Exception|null
     * @since  1.0
     */
    public static function lastException(Exception $e=null)
    {
        if (!is_null($e)) {
            self::instance()->_last_exception = $e;
        }

        return self::instance()->_last_exception;
    }
}
