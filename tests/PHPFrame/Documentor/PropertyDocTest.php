<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_PropertyDocTest extends PHPUnit_Framework_TestCase
{
    private $_prop_doc;

    public function setUp()
    {
        $this->_prop_doc = new PHPFrame_PropertyDoc(
            "PHPFrame_User",
            "fields"
        );
    }

    public function tearDown()
    {
        //...
    }

    public function test_toString()
    {
        $this->assertEquals("fields", (string) $this->_prop_doc);
    }
}
