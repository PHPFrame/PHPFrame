<?php
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$path_array = array_splice($path_array, 0, (count($path_array)-3));
$PHPFrame   = implode(DIRECTORY_SEPARATOR, $path_array).DIRECTORY_SEPARATOR;
$PHPFrame  .= "src".DIRECTORY_SEPARATOR."PHPFrame.php";
require_once $PHPFrame;

class PHPFrame_XMLMapperTest extends PHPUnit_Framework_TestCase
{
    private $_mapper;
    private $_target_class = "PHPFrame_ACL";
    private $_obj;
    
    public function setUp()
    {
        PHPFrame::setTestMode(true);
        
        // Delete the XML file if it already exists
        $xml_file = dirname(__FILE__).DS.$this->_target_class.".xml";
        if (is_file($xml_file)) {
            unlink($xml_file);
        }
        
        // Get mapper and persistent object fixtures
        $this->_mapper = new PHPFrame_Mapper($this->_target_class, dirname(__FILE__));
        $this->_obj    = new $this->_target_class(array(
            "groupid"    => 1, 
            "controller" => "dummy"
        ));
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_insert()
    {
        $this->_mapper->insert($this->_obj);
        $this->assertEquals(1, $this->_obj->getID());
    }
    
    public function test_delete()
    {
        // Insert some objects
        $this->_mapper->insert($this->_obj);
        $this->_mapper->insert(clone $this->_obj);
        $this->_mapper->insert(clone $this->_obj);
        
        // Count stored objects and make sure we got three
        $count = count($this->_mapper->find());
        $this->assertEquals(3, $count);
        
        // Delete object
        $this->_mapper->delete($this->_obj);
        
        // Count objs again and now we should have two
        $count = count($this->_mapper->find());
        $this->assertEquals(2, $count);
    }
    
    public function test_getIdObject()
    {
        $this->setExpectedException('LogicException');
        
        $this->assertType("PHPFrame_XMLIdObject", $this->_mapper->getIdObject());
    }
    
    public function test_find()
    {
        // Insert some objects
        $this->_mapper->insert($this->_obj);
        $this->_mapper->insert(clone $this->_obj);
        $this->_mapper->insert(clone $this->_obj);
        
        // And now we find them with the mapper
        $collection = $this->_mapper->find();
        
        $this->assertType("PHPFrame_PersistentObjectCollection", $collection);
        $this->assertEquals(3, count($collection));
        $this->assertEquals(3, $collection->getTotal());
    }
    
    public function test_findOne()
    {
        // Insert some objects
        $this->_mapper->insert($this->_obj);
        $obj2 = clone $this->_obj;
        $obj2->setController("login");
        $this->_mapper->insert($obj2);
        
        // And now we find the second one with the mapper
        $obj3 = $this->_mapper->findOne(2);
        
        $this->assertType($this->_target_class, $obj3);
        $this->assertEquals("login", $obj3->getController());
    }
    
    public function test_isXML()
    {
        $this->assertTrue($this->_mapper->isXML());
    }
    
    public function test_isSQL()
    {
        $this->assertFalse($this->_mapper->isSQL());
    }
}
