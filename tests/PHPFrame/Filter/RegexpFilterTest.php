<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_RegexpFilterTest extends PHPUnit_Framework_TestCase
{
    private $_filter;

    public function setUp()
    {
        $this->_filter = new PHPFrame_RegexpFilter();
    }

    public function tearDown()
    {
        //...
    }

    public function test_process()
    {
        $values = array("a string", "(*^&%~!?><", "1", "-34", 1, 23, 0664, 0x000A, 3.14, -0.1);
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

    public function test_processFailure()
    {
        $values = array(true, false, array(), new stdClass());
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();

            $this->assertFalse($result);
            $this->assertType("bool", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) > 0));
        }
    }

    public function test_processMatch()
    {
        // Match strings that contain "art"
        $this->_filter->setRegexp("/art/");
        $values = array("art", "fart", "martin", "articulate", "artichoke");
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

    public function test_processMatchFailure()
    {
        // Match strings that contain "art"
        $this->_filter->setRegexp("/art/");
        $values = array("no", "", "blah", array(), new stdClass());
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();

            $this->assertFalse($result);
            $this->assertType("bool", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) > 0));
        }
    }

    public function test_processMinLength()
    {
        // string has to be of minimum 3 chars and contain "art"
        $this->_filter->setMinLength(3);
        $this->_filter->setRegexp("/art/");

        $value    = "artichoke";
        $result   = $this->_filter->process($value);
        $messages = $this->_filter->getMessages();

        $this->assertFalse($result === false);
        $this->assertType("string", $result);
        $this->assertEquals($value, $result);
        $this->assertType("array", $messages);
        $this->assertTrue((count($messages) == 0));
    }

    public function test_processMinLengthFailure()
    {
        // string has to be of minimum 3 chars and contain "art"
        $this->_filter->setMinLength(3);
        $this->_filter->setRegexp("/art/");

        $values = array("a", "won't match");
        for ($i=0; $i<count($values); $i++) {
            $result   = $this->_filter->process($values[$i]);
            $messages = $this->_filter->getMessages();

            $this->assertFalse($result);
            $this->assertType("bool", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == ($i+1)));
            $this->assertTrue(isset($messages[$i][0]));
        }
    }

    public function test_processMaxLength()
    {
        $this->_filter->setMaxLength(12);
        $this->_filter->setRegexp("/oo/");

        $value    = "not too long";
        $result   = $this->_filter->process($value);
        $messages = $this->_filter->getMessages();

        $this->assertFalse($result === false);
        $this->assertType("string", $result);
        $this->assertEquals($value, $result);
        $this->assertType("array", $messages);
        $this->assertTrue((count($messages) == 0));
    }

    public function test_processMaxLengthFailure()
    {
        $this->_filter->setMaxLength(10);
        $this->_filter->setRegexp("/ooo/");

        $values = array("too long perhaps?", "no good");
        for ($i=0; $i<count($values); $i++) {
            $result   = $this->_filter->process($values[$i]);
            $messages = $this->_filter->getMessages();

            $this->assertFalse($result);
            $this->assertType("bool", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == ($i+1)));
            $this->assertTrue(isset($messages[$i][0]));
        }
    }

    public function test_processTruncate()
    {
        $this->_filter->setMaxLength(10);
        $this->_filter->setTruncate(true);

        $value    = "too long perhaps?";
        $result   = $this->_filter->process($value);
        $messages = $this->_filter->getMessages();

        $this->assertFalse($result === false);
        $this->assertType("string", $result);
        $this->assertEquals(substr($value, 0, 10), $result);
        $this->assertTrue((strlen($result) == 10));
    }

    public function test_processStrictFailure()
    {
        $this->_filter->setStrict(true);
        $values = array(true, false, 3, -4, 3.14, array(1,2,3), new stdClass());
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
