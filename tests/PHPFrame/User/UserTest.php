<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_UserTest extends PHPUnit_Framework_TestCase
{
    private $_user;

    public function setUp()
    {
        $this->_user = new PHPFrame_User();
    }

    public function tearDown()
    {
        //...
    }

    public function test_serialisation()
    {
        $serialised   = serialize($this->_user);
        $unserialised = unserialize($serialised);

        $this->assertEquals(
            iterator_to_array($unserialised),
            iterator_to_array($this->_user)
        );
    }

    public function test_validateAll()
    {
        //$this->_user->validateAll();
    }

    public function test_getIterator()
    {
        $array = iterator_to_array($this->_user);

        $this->assertArrayHasKey("group_id", $array);
        $this->assertArrayHasKey("email", $array);
        $this->assertArrayHasKey("password", $array);
        $this->assertArrayHasKey("params", $array);
        $this->assertArrayHasKey("id", $array);
        $this->assertArrayHasKey("ctime", $array);
        $this->assertArrayHasKey("mtime", $array);
        $this->assertArrayHasKey("owner", $array);
        $this->assertArrayHasKey("group", $array);
        $this->assertArrayHasKey("perms", $array);
    }
}
