<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_TextLoggerTest extends PHPUnit_Framework_TestCase
{
    private $_log_file;
    private $_logger;

    public function setUp()
    {
        $this->_log_file = dirname(__FILE__).DS."test.log";
        $this->_logger   = new PHPFrame_TextLogger($this->_log_file);
    }

    public function tearDown()
    {
        if (is_file($this->_log_file)) {
            unlink($this->_log_file);
        }
    }

    public function test_constructorTypeFailure()
    {
        $values = array(null, true, false, 123, 3.14, array(), new stdClass());

        foreach ($values as $value) {
            try {
                $logger = new PHPFrame_TextLogger($value);
            } catch (InvalidArgumentException $e) {
                $this->assertType("InvalidArgumentException", $e);
            }
        }
    }

    public function test_constructFileCreateFailure()
    {
        $this->setExpectedException("PHPUnit_Framework_Error");

        $logger = new PHPFrame_TextLogger("/aaa");
    }

    public function test_constructFileNotWriteableFailure()
    {
        $log_file = PHPFrame_Filesystem::getSystemTempDir().DS."test.log";

        touch($log_file);
        chmod($log_file, 0444);

        try {
            $logger = new PHPFrame_TextLogger($log_file);
        } catch (PHPUnit_Framework_Error $e) {
            chmod($log_file, 0644);
            unlink($log_file);
        }
    }

    public function test_write()
    {
        $this->_logger->write("Some message");

        $log_contents = iterator_to_array($this->_logger);

        $this->assertEquals(3, count($log_contents));
        $this->assertEquals(1, preg_match('/Some message$/', $log_contents[2]));
    }

    public function test_writeArray()
    {
        $this->_logger->write(array(
            "Some message",
            "another message",
            "and another one..."
        ));

        $log_contents = iterator_to_array($this->_logger);

        $this->assertRegExp(
            "/Some message\sanother message\sand another one\.\.\./",
            implode("", $log_contents)
        );
    }

    public function test_writeWithRemoreAddr()
    {
        $_SERVER['REMOTE_ADDR'] = "127.0.0.1";

        $this->_logger->write("Some message");

        $log_contents = iterator_to_array($this->_logger);

        $this->assertRegExp(
            "/\[ip:127\.0\.0\.1\]/",
            implode("", $log_contents)
        );

        unset($_SERVER['REMOTE_ADDR'] );
    }

    public function test_serialize()
    {
        $serialised   = serialize($this->_logger);
        $unserialised = unserialize($serialised);

        $this->assertTrue($this->_logger == $unserialised);
    }

    public function test_getAndSetLogLevel()
    {
        $log_level = $this->_logger->logLevel();

        $this->_logger->logLevel(5);
        $this->assertEquals(5, $this->_logger->logLevel());

        $this->_logger->logLevel(1);
        $this->assertEquals(1, $this->_logger->logLevel());
    }

    public function test_setLogLevelNegativeIntFailure()
    {
        $this->setExpectedException("InvalidArgumentException");

        $this->_logger->logLevel(-1);
    }

    public function test_setLogLevelValueTooHighFailure()
    {
        $this->setExpectedException("InvalidArgumentException");

        $this->_logger->logLevel(6);
    }

    public function test_setLogLevelBoolTypeFailure()
    {
        $this->setExpectedException("InvalidArgumentException");

        $this->_logger->logLevel(true);
    }

    public function test_setLogLevelFloatTypeFailure()
    {
        $this->setExpectedException("InvalidArgumentException");

        $this->_logger->logLevel(3.14);
    }

    public function test_setLogLevelStringTypeFailure()
    {
        $this->setExpectedException("InvalidArgumentException");

        $this->_logger->logLevel("some string");
    }

    public function test_setLogLevelArrayTypeFailure()
    {
        $this->setExpectedException("InvalidArgumentException");

        $this->_logger->logLevel(array());
    }

    public function test_setLogLevelObjectTypeFailure()
    {
        $this->setExpectedException("InvalidArgumentException");

        $this->_logger->logLevel(new stdClass());
    }
}
