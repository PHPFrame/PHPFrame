<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_PersistentObjectCollectionTest extends PHPUnit_Framework_TestCase
{
    private $_obj, $_db_path, $_db;

    public function setUp()
    {
        $this->_db_path = PHPFrame_Filesystem::getSystemTempDir().DS."test.db";
        $this->_db = PHPFrame_Database::getInstance("sqlite:".$this->_db_path);
        $factory = new PHPFrame_SQLPersistenceFactory("PHPFrame_User", "#__users", $this->_db);
        $obj_factory = new PHPFrame_PersistentObjectFactory($factory);
        $this->_obj = new PHPFrame_PersistentObjectCollection(array(), $obj_factory);
    }

    public function tearDown()
    {
        if (is_file($this->_db_path)) {
            unlink($this->_db_path);
        }
    }

    public function test_serialise()
    {
        $serialised = serialize($this->_obj);
        $unserialised = unserialize($serialised);

        $this->_db->connect();

        $this->assertEquals($this->_obj, $unserialised);
    }
}
