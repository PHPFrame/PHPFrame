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

    public function test_getCryptedPassword()
    {
        $pass = $this->_crypt->getCryptedPassword("MyPassword");
        $this->assertType("string", $pass);
        $this->assertTrue(strlen($pass) == 32);
    }

    public function test_getSalt()
    {
        $this->assertEquals(2, strlen($this->_crypt->getSalt("crypt")));
        $this->assertEquals(4, strlen($this->_crypt->getSalt("ssha")));
        $this->assertEquals(4, strlen($this->_crypt->getSalt("smd5")));
        $this->assertEquals(8, strlen($this->_crypt->getSalt("aprmd5")));
        $this->assertEquals(12, strlen($this->_crypt->getSalt("crypt-md5")));
        $this->assertEquals(16, strlen($this->_crypt->getSalt("crypt-blowfish")));
        $this->assertEquals(32, strlen($this->_crypt->getSalt()));
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

    public function test_encryptPassword()
    {
        $plain_text = "Passw0rd";
        $encrypted  = $this->_crypt->encryptPassword($plain_text);

        $this->assertTrue(strlen($encrypted) == 65);

        $parts     = explode(':', $encrypted);
        $crypt     = $parts[0];
        $salt      = @$parts[1];
        $testcrypt = $this->_crypt->getCryptedPassword($plain_text, $salt);

        $this->assertEquals($crypt, $testcrypt);
    }
}
