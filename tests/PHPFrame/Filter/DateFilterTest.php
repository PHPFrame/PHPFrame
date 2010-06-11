<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_DateFilterTest extends PHPUnit_Framework_TestCase
{
    private $_filter;

    public function setUp()
    {
        $this->_filter = new PHPFrame_DateFilter();
    }

    public function tearDown()
    {
        //...
    }

    public function test_processDate()
    {
        $this->_filter->setFormat(PHPFrame_DateFilter::FORMAT_DATE);
        $values = array("2010-02-04", "1978-07-15");
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();

            $this->assertTrue($result !== false);
            $this->assertType("string", $result);
            $this->assertEquals((string) $value, $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == 0));
        }
    }

    public function test_processDateFailure()
    {
        $this->_filter->setFormat(PHPFrame_DateFilter::FORMAT_DATE);
        $values = array(true, false, 1, 100, 0.0, 1.0, 3.14, "some string", array(), new stdClass());
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();

            $this->assertFalse($result);
            $this->assertType("bool", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) > 0));
        }
    }

    public function test_processDatetime()
    {
        $this->_filter->setFormat(PHPFrame_DateFilter::FORMAT_DATETIME);
        $values = array("2010-02-04 23:34:57", "1978-07-15 00:00:00");
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();

            $this->assertTrue($result !== false);
            $this->assertType("string", $result);
            $this->assertEquals((string) $value, $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == 0));
        }
    }

    public function test_processDatetimeFailure()
    {
        $this->_filter->setFormat(PHPFrame_DateFilter::FORMAT_DATETIME);
        $values = array(true, false, 1, 100, 0.0, 1.0, 3.14, "some string", array(), new stdClass());
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();

            $this->assertFalse($result);
            $this->assertType("bool", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) > 0));
        }
    }

    public function test_processTime()
    {
        $this->_filter->setFormat(PHPFrame_DateFilter::FORMAT_TIME);
        $values = array("23:34:57", "00:00:00");
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();

            $this->assertTrue($result !== false);
            $this->assertType("string", $result);
            $this->assertEquals((string) $value, $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == 0));
        }
    }

    public function test_processTimeFailure()
    {
        $this->_filter->setFormat(PHPFrame_DateFilter::FORMAT_TIME);
        $values = array(true, false, 1, 100, 0.0, 1.0, 3.14, "some string", array(), new stdClass());
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
