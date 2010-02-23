<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_FileRegistryTest extends PHPUnit_Framework_TestCase
{
    private $_cache_file, $_registry;
    
    public function setUp()
    {
        PHPFrame::testMode(true);
        
        $this->_cache_file = dirname(__FILE__).DS."test.reg";
        
        if (is_file($this->_cache_file)) {
            unlink($this->_cache_file);
        }
        
        $this->_registry = new PHPFrame_FileRegistry($this->_cache_file);
    }
    
    public function tearDown()
    {
        if (isset($this->_registry)) {
            unset($this->_registry);
        }
        
        if (is_file($this->_cache_file)) {
            unlink($this->_cache_file);
        }
    }
    
    public function test_()
    {
        // Add a bool to registry
        $this->_registry->set("bool", true);
        
        // Add an int
        $this->_registry->set("int", 123);
        
        // Add a float
        $this->_registry->set("float", 3.14);
        
        // Add basic array
        $this->_registry->set("array", array(1,2,3));
        
        // Add multimensional array
        $array2 = array(1,2, array(3,4,"foo"=>"bar"));
        $this->_registry->set("array2", $array2);
        
        // Add an object 
        $obj = new stdClass();
        $obj->foo = 1;
        $obj->bar = "some string";
        $this->_registry->set("obj", $obj);
        
        // Unset the registry (the destructor should write the data to file)
        unset($this->_registry);
        
        // Get new instance of registry and data should now be read from file
        $this->_registry = new PHPFrame_FileRegistry($this->_cache_file);
        
        $this->assertType("bool", $this->_registry->get("bool"));
        $this->assertEquals(true, $this->_registry->get("bool"));
        
        $this->assertType("int", $this->_registry->get("int"));
        $this->assertEquals(123, $this->_registry->get("int"));
        
        $this->assertType("float", $this->_registry->get("float"));
        $this->assertEquals(3.14, $this->_registry->get("float"));
        
        $this->assertType("array", $this->_registry->get("array"));
        $this->assertEquals(array(1,2,3), $this->_registry->get("array"));
        
        $this->assertType("array", $this->_registry->get("array2"));
        $this->assertEquals($array2, $this->_registry->get("array2"));
        
        $this->assertType("stdClass", $this->_registry->get("obj"));
        $this->assertEquals($obj, $this->_registry->get("obj"));
        
        
        // Add another var to registry
        $this->_registry->set("another var", "blah blah");
        
        // Unset the registry (the destructor should write the data to file)
        unset($this->_registry);
        
        // Get new instance of registry and data should now be read from file
        $this->_registry = new PHPFrame_FileRegistry($this->_cache_file);
        
        $this->assertType("string", $this->_registry->get("another var"));
        $this->assertEquals("blah blah", $this->_registry->get("another var"));
    }
}
