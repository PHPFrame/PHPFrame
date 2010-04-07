<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_ExceptionHandlerTest extends PHPUnit_Framework_TestCase
{
    private $_handler;

    public function setUp()
    {
        $this->_handler = PHPFrame_ExceptionHandler::instance();
    }

    public function tearDown()
    {
        $this->_handler->restore();
    }

    public function test_instance()
    {
        $handler = PHPFrame_ExceptionHandler::instance();
        $this->assertEquals($this->_handler, $handler);
    }

    public function test_restore()
    {
        $this->assertFalse($this->_handler->catchableErrorsToExceptions());
        $this->assertTrue($this->_handler->catchableErrorsToExceptions(true));
        $this->_handler->restore();

        $handler = PHPFrame_ExceptionHandler::instance();
        $this->assertFalse($handler->catchableErrorsToExceptions());
    }

    public function test_catchableErrorsToExceptions()
    {
        $this->assertFalse($this->_handler->catchableErrorsToExceptions());
        $this->assertTrue($this->_handler->catchableErrorsToExceptions(true));
        $this->assertTrue($this->_handler->catchableErrorsToExceptions());
        $this->assertFalse($this->_handler->catchableErrorsToExceptions(false));
        $this->assertFalse($this->_handler->catchableErrorsToExceptions());
    }

    public function test_handleError()
    {
        $this->_handler->catchableErrorsToExceptions(true);

        $this->setExpectedException("PHPFrame_ErrorException");

        // This will throw a catchable error
        $a[1];
    }

    public function test_handleException()
    {
        $this->_handler->displayExceptions(false);

        ob_start();
        $this->_handler->handleException(new Exception("This is a test exception"));
        $output = ob_get_clean();

        $this->assertRegExp("/Ooops\.\.\. an error occurred/", $output);
        $this->assertRegExp("/I'm afraid something went wrong/", $output);

        $this->_handler->displayExceptions(true);

        ob_start();
        $this->_handler->handleException(new Exception("This is a test exception"));
        $output = ob_get_clean();

        $this->assertRegExp("/Ooops\.\.\. an error occurred/", $output);
        $this->assertRegExp("/Uncaught Exception: This is a test exception/", $output);
    }

    public function test_displayExceptions()
    {
        $this->assertTrue($this->_handler->displayExceptions());
        $this->assertFalse($this->_handler->displayExceptions(false));
        $this->assertFalse($this->_handler->displayExceptions());
        $this->assertTrue($this->_handler->displayExceptions(true));
        $this->assertTrue($this->_handler->displayExceptions());
    }

    public function test_lastException()
    {
        $this->assertNull($this->_handler->lastException());

        ob_start();
        $this->_handler->handleException(new Exception("This is a test exception", 500));
        ob_end_clean();

        $this->assertType("Exception", $this->_handler->lastException());
        $this->assertEquals("This is a test exception", $this->_handler->lastException()->getMessage());
        $this->assertEquals(500, $this->_handler->lastException()->getCode());
    }
}
