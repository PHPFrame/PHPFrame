<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_StringFilterTest extends PHPUnit_Framework_TestCase
{
    private $_filter;

    public function setUp()
    {
        $this->_filter = new PHPFrame_StringFilter();
    }

    public function tearDown()
    {
        //...
    }

    public function test_constructWithOptions()
    {
        $filter_options = array("min_length"=>6, "max_length"=>10, "strict"=>true);
        $filter         = new PHPFrame_StringFilter($filter_options);

        $this->assertEquals(6, $filter->getOption("min_length"));
        $this->assertEquals(10, $filter->getOption("max_length"));
        $this->assertEquals(false, $filter->getOption("truncate"));
        $this->assertEquals(true, $filter->getOption("strict"));
    }

    public function test_getOption()
    {
        // Check that filter has default options
        $this->assertEquals(0, $this->_filter->getOption("min_length"));
        $this->assertEquals(-1, $this->_filter->getOption("max_length"));
        $this->assertEquals(false, $this->_filter->getOption("truncate"));
        $this->assertEquals(false, $this->_filter->getOption("strict"));
    }

    public function test_getOptions()
    {
        $this->assertArrayHasKey("min_length", $this->_filter->getOptions());
        $this->assertArrayHasKey("max_length", $this->_filter->getOptions());
        $this->assertArrayHasKey("truncate", $this->_filter->getOptions());
        $this->assertArrayHasKey("strict", $this->_filter->getOptions());
    }

    public function test_setMinLength()
    {
        $this->_filter->setMinLength(3);
        $this->assertEquals(3, $this->_filter->getOption("min_length"));
    }

    public function test_setMaxLength()
    {
        $this->_filter->setMaxLength(10);
        $this->assertEquals(10, $this->_filter->getOption("max_length"));
    }

    public function test_setTruncate()
    {
        $this->_filter->setTruncate(true);
        $this->assertEquals(true, $this->_filter->getOption("truncate"));
    }

    public function test_setStrict()
    {
        $this->_filter->setStrict(true);
        $this->assertEquals(true, $this->_filter->getOption("strict"));
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

    public function test_processMinLength()
    {
        $this->_filter->setMinLength(3);

        $result   = $this->_filter->process("im more than 3 chars long");
        $messages = $this->_filter->getMessages();

        $this->assertFalse($result === false);
        $this->assertType("string", $result);
        $this->assertType("array", $messages);
        $this->assertTrue((count($messages) == 0));
    }

    public function test_processMinLengthFailure()
    {
        $this->_filter->setMinLength(3);

        $result   = $this->_filter->process("a");
        $messages = $this->_filter->getMessages();

        $this->assertFalse($result);
        $this->assertType("bool", $result);
        $this->assertType("array", $messages);
        $this->assertTrue((count($messages) > 0));
        $this->assertTrue(isset($messages[0][0]));
        $this->assertTrue(isset($messages[0][1]));
        $this->assertEquals("LengthException", $messages[0][1]);
    }

    public function test_processMaxLength()
    {
        $this->_filter->setMaxLength(12);

        $result   = $this->_filter->process("not too long");
        $messages = $this->_filter->getMessages();

        $this->assertFalse($result === false);
        $this->assertType("string", $result);
        $this->assertType("array", $messages);
        $this->assertTrue((count($messages) == 0));
    }

    public function test_processMaxLengthFailure()
    {
        $this->_filter->setMaxLength(10);

        $result   = $this->_filter->process("too long perhaps?");
        $messages = $this->_filter->getMessages();

        $this->assertFalse($result);
        $this->assertType("bool", $result);
        $this->assertType("array", $messages);
        $this->assertTrue((count($messages) > 0));
        $this->assertTrue(isset($messages[0][0]));
        $this->assertTrue(isset($messages[0][1]));
        $this->assertEquals("LengthException", $messages[0][1]);
    }

    public function test_processTruncate()
    {
        $this->_filter->setMaxLength(10);
        $this->_filter->setTruncate(true);

        $result   = $this->_filter->process("too long perhaps?");
        $messages = $this->_filter->getMessages();

        $this->assertFalse($result === false);
        $this->assertType("string", $result);
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
