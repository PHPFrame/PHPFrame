<?php
// Include framework if not inculded yet
require_once preg_replace("/data\/.*/", "src/PHPFrame.php", __FILE__);

class AppControllerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        //...
        $this->_bootstrap = preg_replace("/tests\/.*/", "public/index.php", __FILE__);
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_create()
    {
        $install_dir = PHPFrame_Filesystem::getSystemTempDir().DS."newapp";
        
        if (is_dir($install_dir)) {
            PHPFrame_Filesystem::rm($install_dir, true);
        }
        
        mkdir($install_dir);
        
        $cmd        = "cd ".$install_dir." && ";
        $cmd       .= "php ".$this->_bootstrap." app create app_name=MyApp";
        $output     = null;
        $return_var = null;
        
        exec($cmd, $output, $return_var);
        
        $this->assertTrue(is_dir($install_dir.DS."etc"));
        $this->assertTrue(is_dir($install_dir.DS."public"));
        $this->assertTrue(is_dir($install_dir.DS."src"));
        $this->assertTrue(is_file($install_dir.DS."etc".DS."phpframe.ini"));
        
        $this->assertType("array", $output);
        
        // Grab last line of output ignoring empty line
        foreach(array_reverse($output) as $line) {
            if (empty($line)) {
                continue;
            }
            
            break;
        }
        
        $this->assertEquals(0, $return_var);
        $this->assertRegExp("/^SUCCESS: /", $line);
        
        PHPFrame_Filesystem::rm($install_dir, true);
    }
    
    public function test_remove()
    {
        // First we create an app
        $install_dir = PHPFrame_Filesystem::getSystemTempDir().DS."newapp";
        
        if (is_dir($install_dir)) {
            PHPFrame_Filesystem::rm($install_dir, true);
        }
        
        mkdir($install_dir);
        
        $cmd        = "cd ".$install_dir." && ";
        $cmd       .= "php ".$this->_bootstrap." app create app_name=MyApp";
        $output     = null;
        $return_var = null;
        
        exec($cmd, $output, $return_var);
        
        $this->assertTrue(is_dir($install_dir.DS."etc"));
        $this->assertTrue(is_dir($install_dir.DS."public"));
        $this->assertTrue(is_dir($install_dir.DS."src"));
        $this->assertTrue(is_file($install_dir.DS."etc".DS."phpframe.ini"));
        
        $this->assertType("array", $output);
        
        // Grab last line of output ignoring empty line
        foreach(array_reverse($output) as $line) {
            if (empty($line)) {
                continue;
            }
            
            break;
        }
        
        $this->assertEquals(0, $return_var);
        $this->assertRegExp("/^SUCCESS: /", $line);
        
        // Now we remove app
        $cmd        = "cd ".$install_dir." && ";
        $cmd       .= "php ".$this->_bootstrap." app remove";
        $output     = null;
        $return_var = null;
        
        exec($cmd, $output, $return_var);
        
        $this->assertFalse(is_dir($install_dir.DS."etc"));
        $this->assertFalse(is_dir($install_dir.DS."public"));
        $this->assertFalse(is_dir($install_dir.DS."src"));
        
        $this->assertType("array", $output);
        
        // Grab last line of output ignoring empty line
        foreach(array_reverse($output) as $line) {
            if (empty($line)) {
                continue;
            }
            
            break;
        }
        
        $this->assertEquals(0, $return_var);
        $this->assertRegExp("/^SUCCESS: /", $line);
    }
}
