<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_ImageProcessorTest extends PHPUnit_Framework_TestCase
{
    private $_obj;

    public function setUp()
    {
        $this->_obj = new PHPFrame_ImageProcessor();
    }

    public function tearDown()
    {
        //...
    }

    public function test_()
    {
        //var_dump($this->_obj);
    }
}
