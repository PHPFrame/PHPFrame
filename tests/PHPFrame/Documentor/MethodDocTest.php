<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_MethodDocTest extends PHPUnit_Framework_TestCase
{
    private $_method_doc;

    public function setUp()
    {
        $this->_method_doc = new PHPFrame_MethodDoc(
            "PHPFrame_User",
            "__construct"
        );
    }

    public function tearDown()
    {
        //...
    }

    public function test_toString()
    {
        $this->assertRegExp(
            "/".preg_quote("__construct([ array options=null ])")."/",
            (string) $this->_method_doc
        );
    }
}
