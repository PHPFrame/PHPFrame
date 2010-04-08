<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_ClassDocTest extends PHPUnit_Framework_TestCase
{
    private $_class_doc;

    public function setUp()
    {
        $this->_class_doc = new PHPFrame_ClassDoc("PHPFrame_User");
    }

    public function tearDown()
    {
        //...
    }

    public function test_toString()
    {
        $str = (string) $this->_class_doc;
        $this->assertType("string", $str);
        $this->assertRegExp(
            "/Class: PHPFrame_User\n--------------/",
            $str
        );
    }

    public function test_getProperties()
    {
        $this->assertType("array", $this->_class_doc->getProperties());
    }

    public function test_getMethods()
    {
        $methods = $this->_class_doc->getMethods();
        $this->assertType("array", $methods);
        $this->assertTrue(count($methods) > 0);
        $this->assertType("PHPFrame_MethodDoc", $methods[0]);
    }
}
