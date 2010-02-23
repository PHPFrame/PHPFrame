<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_SQLMapperTest extends PHPUnit_Framework_TestCase
{
    private $_target_class;
    private $_db_file;
    private $_mapper;
    private $_obj;
    
    public function __construct()
    {
        $this->_target_class = "PHPFrame_ACL";
        $this->_db_file      = dirname(__FILE__).DS."test.sqlite";
    }
    
    public function setUp()
    {
        PHPFrame::testMode(true);
        
        // Delete the db file if it already exists
        if (is_file($this->_db_file)) {
            unlink($this->_db_file);
        }
        
        $this->_obj = new $this->_target_class(array(
            "groupid"    => 1, 
            "controller" => "dummy"
        ));
        
        // Get db object
        $db  = PHPFrame_Database::getInstance("sqlite:".$this->_db_file);
        
        // Drop the table if it exists
        if ($db->hasTable($this->_target_class)) {
            $db->dropTable($this->_target_class);
        }
        
        // Create db table
        $or_toolbox = new PHPFrame_ObjectRelationalToolbox();
        $or_toolbox->createTable($db, $this->_obj);
        
        // Get mapper fixture
        $this->_mapper = new PHPFrame_Mapper($this->_target_class, $db);
    }
    
    public function tearDown()
    {
        unset($this->_obj);
        unset($this->_mapper);
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
        $this->assertType("PHPFrame_SQLIdObject", $this->_mapper->getIdObject());
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
        $this->assertFalse($this->_mapper->isXML());
    }
    
    public function test_isSQL()
    {
        $this->assertTrue($this->_mapper->isSQL());
    }
}
