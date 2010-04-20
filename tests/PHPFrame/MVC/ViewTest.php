<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_ViewTest extends PHPUnit_Framework_TestCase
{
    private $_view;

    public function setUp()
    {
        PHPFrame::testMode(true);

        $this->_view = new PHPFrame_View("test", array("somevar"=>"somevalue"));
    }

    public function tearDown()
    {
        //...
    }

    public function test_()
    {
        $this->_view->addData("some_key","Some value");

        $array = iterator_to_array($this->_view);
        $this->assertType("array", $array);
        $this->assertEquals(2, count($array));
        $this->assertArrayHasKey("somevar", $array);
        $this->assertArrayHasKey("some_key", $array);
    }
}
