<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_ConfigTest extends PHPUnit_Framework_TestCase
{
    private $_config_file, $_config;

    public function __construct()
    {
        $this->_config_file  = preg_replace("/tests\/.*/", "data", __FILE__);
        $this->_config_file .= "/CLI_Tool/etc/phpframe.ini";
    }

    public function setUp()
    {

        $this->_config = new PHPFrame_Config($this->_config_file);
    }

    public function tearDown()
    {
        //...
    }

    public function test_constructFailureNoFile()
    {
        $this->setExpectedException("RuntimeException");

        $config = new PHPFrame_Config("aaa");
    }

    public function test_constructFailureBadData()
    {
        $this->setExpectedException("RuntimeException");

        $tmp_file = PHPFrame_Filesystem::getSystemTempDir().DS."phpframe.ini.tmp";
        if (is_file($tmp_file)) {
            PHPFrame_Filesystem::rm($tmp_file);
        }

        file_put_contents($tmp_file, "lskjdnskjnskdjkj");

        $config = new PHPFrame_Config($tmp_file);
    }

    public function test_toString()
    {
        $this->assertRegExp(
            "/\[general\]\s+app_name = PHPFrame Command Line Tool/",
            (string) $this->_config
        );
    }

    public function test_getIterator()
    {
        $array = iterator_to_array($this->_config);

        $this->assertArrayHasKey("app_name", $array);
        $this->assertArrayHasKey("version", $array);
        $this->assertArrayHasKey("base_url", $array);
        $this->assertArrayHasKey("theme", $array);
        $this->assertArrayHasKey("default_lang", $array);
        $this->assertArrayHasKey("secret", $array);
        $this->assertArrayHasKey("timezone", $array);
        $this->assertArrayHasKey("default_controller", $array);
    }

    public function test_get()
    {
        //...
    }

    public function test_set()
    {
        //...
    }

    public function test_bind()
    {
        $app_name  = $this->_config->get("app_name");
        $imap_pass = $this->_config->get("imap.pass");

        $array = array("app_name"=>"New app name", "imap.pass"=>"somepassword");
        $this->_config->bind($array);

        $app_name_updated  = $this->_config->get("app_name");
        $imap_pass_updated = $this->_config->get("imap.pass");

        $this->assertNotEquals($app_name, $app_name_updated);
        $this->assertNotEquals($imap_pass, $imap_pass_updated);
    }

    public function test_bindUnderscores()
    {
        $app_name  = $this->_config->get("app_name");
        $imap_pass = $this->_config->get("imap.pass");

        $array = array("app_name"=>"New app name", "imap_pass"=>"somepassword");
        $this->_config->bind($array);

        $app_name_updated  = $this->_config->get("app_name");
        $imap_pass_updated = $this->_config->get("imap.pass");

        $this->assertNotEquals($app_name, $app_name_updated);
        $this->assertNotEquals($imap_pass, $imap_pass_updated);
    }

    public function test_getSections()
    {
        $sections = $this->_config->getSections();
        $this->assertType("array", $sections);

        $this->assertContains("general", $sections);
        $this->assertContains("filesystem", $sections);
        $this->assertContains("debug", $sections);
        $this->assertContains("sources", $sections);
        $this->assertContains("db", $sections);
        $this->assertContains("smtp", $sections);
        $this->assertContains("imap", $sections);
    }

    public function test_getSection()
    {
        $section = $this->_config->getSection("smtp");
        $this->assertType("array", $section);

        $this->assertArrayHasKey("mailer", $section);
        $this->assertArrayHasKey("host", $section);
        $this->assertArrayHasKey("port", $section);
        $this->assertArrayHasKey("auth", $section);
        $this->assertArrayHasKey("user", $section);
        $this->assertArrayHasKey("pass", $section);
        $this->assertArrayHasKey("fromaddress", $section);
        $this->assertArrayHasKey("fromname", $section);
    }

    public function test_getSectionFailure()
    {
        $this->setExpectedException("RuntimeException");

        $section = $this->_config->getSection("aaaa");
    }

    public function test_getKeys()
    {
        $keys = $this->_config->getKeys();
        $this->assertType("array", $keys);

        $this->assertContains("app_name", $keys);
        $this->assertContains("version", $keys);
        $this->assertContains("base_url", $keys);
        $this->assertContains("theme", $keys);
        $this->assertContains("default_lang", $keys);
        $this->assertContains("secret", $keys);
        $this->assertContains("timezone", $keys);
        $this->assertContains("default_controller", $keys);

        $this->assertContains("debug.display_exceptions", $keys);
        $this->assertContains("debug.log_level", $keys);
    }

    public function test_keyExists()
    {
        $this->assertFalse($this->_config->keyExists("sss"));

        $this->assertTrue($this->_config->keyExists("app_name"));
        $this->assertTrue($this->_config->keyExists("imap.enable"));
    }

    public function test_keyExistsEnsureFailure()
    {
        $this->setExpectedException("LogicException");

        $this->_config->keyExists("sss", true);
    }

    public function test_getPath()
    {
        $this->assertTrue(is_file($this->_config->getPath()));
    }

    public function test_store()
    {
        $this->assertEquals("PHPFrame Command Line Tool", $this->_config->get("app_name"));

        $this->_config->set("app_name", "aaa");

        $this->assertEquals("aaa", $this->_config->get("app_name"));

        // Store somewhere else
        $tmp_file = PHPFrame_Filesystem::getSystemTempDir().DS."phpframe.ini.tmp";
        if (is_file($tmp_file)) {
            PHPFrame_Filesystem::rm($tmp_file);
        }
        $this->_config->store($tmp_file);

        $config2 = new PHPFrame_Config($tmp_file);

        $this->assertEquals("aaa", $config2->get("app_name"));

        $config2->set("app_name", "bbb");

        $this->assertEquals("bbb", $config2->get("app_name"));

        $config2->store();

        $config3 = new PHPFrame_Config($tmp_file);

        $this->assertEquals("bbb", $config3->get("app_name"));
    }
}
