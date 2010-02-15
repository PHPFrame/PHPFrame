<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_ConfigTest extends PHPUnit_Framework_TestCase
{
    private $_config_file, $_config;
    
    public function __construct()
    {
        $this->_config_file  = preg_replace("/tests\/.*/", "data", __FILE__);
        $this->_config_file .= "/etc/phpframe.ini";
    }
    
    public function setUp()
    {
        
        $this->_config = new PHPFrame_Config($this->_config_file);
    }
    
    public function tearDown()
    {
        //...
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
        $this->assertArrayHasKey("ignore_acl", $array);
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
        $this->assertContains("ignore_acl", $keys);
        
        $this->assertContains("debug.display_exceptions", $keys);
        $this->assertContains("debug.log_level", $keys);
    }
    
    public function test_keyExists()
    {
        
    }
    
    public function test_store()
    {
        
    }
}
