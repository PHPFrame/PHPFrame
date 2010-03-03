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
        
        $this->assertEquals($unserialised, $this->_user);
    }
    
    public function test_validateAll()
    {
        //$this->_user->validateAll();
    }
    
    public function test_getIterator()
    {
        $array = iterator_to_array($this->_user);
        
        $this->assertArrayHasKey("groupid", $array);
        $this->assertArrayHasKey("username", $array);
        $this->assertArrayHasKey("password", $array);
        $this->assertArrayHasKey("email", $array);
        $this->assertArrayHasKey("block", $array);
        $this->assertArrayHasKey("last_visit", $array);
        $this->assertArrayHasKey("params", $array);
        $this->assertArrayHasKey("deleted", $array);
        $this->assertArrayHasKey("id", $array);
        $this->assertArrayHasKey("atime", $array);
        $this->assertArrayHasKey("ctime", $array);
        $this->assertArrayHasKey("mtime", $array);
        $this->assertArrayHasKey("owner", $array);
        $this->assertArrayHasKey("group", $array);
        $this->assertArrayHasKey("perms", $array);
    }
}
