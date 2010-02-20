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
        //...
    }
    
    public function catchableErrorsToExceptions()
    {
        $this->assertFalse($this->_handler->catchableErrorsToExceptions());
        $this->assertTrue($this->_handler->catchableErrorsToExceptions(true));
        $this->assertTrue($this->_handler->catchableErrorsToExceptions());
        $this->assertFalse($this->_handler->catchableErrorsToExceptions(false));
        $this->assertFalse($this->_handler->catchableErrorsToExceptions());
    }
    
    public function test_handleError()
    {
        //...
    }
    
    public function test_handleException()
    {
        //...
    }
    
    public function test_displayExceptions()
    {
        $this->assertTrue($this->_handler->displayExceptions());
        $this->assertFalse($this->_handler->displayExceptions(false));
        $this->assertFalse($this->_handler->displayExceptions());
        $this->assertTrue($this->_handler->displayExceptions(true));
        $this->assertTrue($this->_handler->displayExceptions());
    }
}
