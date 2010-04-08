<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_URLFilterTest extends PHPUnit_Framework_TestCase
{
    private $_filter;

    public function setUp()
    {
        $this->_filter = new PHPFrame_URLFilter();
    }

    public function tearDown()
    {
        //...
    }

    public function test_process()
    {
        $values = array(
            "http://www.phpframe.org",
            "ftp://ftp.example.com",
            "ssl://www.phpframe.org/api"
        );

        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();

            $this->assertFalse($result === false);
            $this->assertType("string", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == 0));
        }
    }

    public function test_processFailure()
    {
        $values = array(
            true,
            -3,
            3.14,
            "http:/w.cccc.com",
            "www.phpframe.org",
            ".com",
            array(),
            new stdClass()
        );

        for ($i=0; $i<count($values); $i++) {
            $result   = $this->_filter->process($values[$i]);
            $messages = $this->_filter->getMessages();

            $this->assertFalse($result);
            $this->assertType("bool", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == ($i+1)));
        }
    }

    public function test_processPathRequired()
    {
        $this->_filter->setPathRequired(true);

        $values = array(
            "http://www.phpframe.org/api",
            "http://www.phpframe.org/index.php",
            "http://www.phpframe.org/",
            "ftp://www.phpframe.org/downloads/somefile.tgz"
        );

        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();

            $this->assertFalse($result === false);
            $this->assertType("string", $result);
            $this->assertEquals($value, $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == 0));
        }
    }

    public function test_processPathRequiredFailure()
    {
        $this->_filter->setPathRequired(true);

        $values = array(
            "http://www.phpframe.org",
            "http://phpframe.org",
            "http://localhost",
            "ftp://www.phpframe.org"
        );

        for ($i=0; $i<count($values); $i++) {
            $result   = $this->_filter->process($values[$i]);
            $messages = $this->_filter->getMessages();

            $this->assertFalse($result);
            $this->assertType("bool", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == ($i+1)));
        }
    }

    public function test_processQueryRequired()
    {
        $this->_filter->setQueryRequired(true);

        $values = array(
            "http://www.phpframe.org/api/index.php?controller=dummy",
            "http://phpframe.org/index.php?id=1&cat=2&section=21",
            "https://www.phpframe.org/?some_var=something",
            "http://www.phpframe.org/downloads?file=somefile.tgz"
        );

        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();

            $this->assertFalse($result === false);
            $this->assertType("string", $result);
            $this->assertEquals($value, $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == 0));
        }
    }

    public function test_processQueryRequiredFailure()
    {
        $this->_filter->setQueryRequired(true);

        $values = array(
            "http://www.phpframe.org/api/index.php/controller=dummy",
            "http://phpframe.org/index.php&id=1&cat=2&section=21",
            "https://www.phpframe.org/",
            "http://www.phpframe.org/downloads",
            "http://www.phpframe.org/index.php"
        );

        for ($i=0; $i<count($values); $i++) {
            $result   = $this->_filter->process($values[$i]);
            $messages = $this->_filter->getMessages();

            $this->assertFalse($result);
            $this->assertType("bool", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == ($i+1)));
        }
    }
}
