<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_ClassDocTest extends PHPUnit_Framework_TestCase
{
    private $_class_doc;

    public function setUp()
    {
        $this->_class_doc = new PHPFrame_ClassDoc("PHPFrame_Application");
    }

    public function tearDown()
    {
        //...
    }

    public function test_()
    {
        //echo $this->_class_doc;
    }
}
