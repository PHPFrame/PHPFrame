<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_CryptTest extends PHPUnit_Framework_TestCase
{
    private $_crypt;

    public function setUp()
    {
        $this->_crypt = new PHPFrame_Crypt("some secret");
    }

    public function tearDown()
    {
        //...
    }

    public function test_getHash()
    {
        $hash = $this->_crypt->getHash("I am a seed");
        $this->assertType("string", $hash);
        $this->assertTrue(strlen($hash) == 32);
    }

    public function test_encryptPassword()
    {
        $salt = $this->_crypt->genRandomPassword(32);
        $pass = $this->_crypt->encryptPassword("MyPassword", $salt);

        $this->assertType("string", $pass);
        $this->assertTrue(strlen($pass) == 32);
        $this->assertEquals(md5("MyPassword".$salt), $pass);
    }

    public function test_genRandomPassword()
    {
        $pass = $this->_crypt->genRandomPassword();
        $this->assertType("string", $pass);
        $this->assertTrue(strlen($pass) == 8);

        $pass = $this->_crypt->genRandomPassword(16);
        $this->assertType("string", $pass);
        $this->assertTrue(strlen($pass) == 16);

        $pass = $this->_crypt->genRandomPassword(32);
        $this->assertType("string", $pass);
        $this->assertTrue(strlen($pass) == 32);
    }
}
