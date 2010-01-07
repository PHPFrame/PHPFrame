<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_MySQLDatabaseTest extends PHPUnit_Framework_TestCase
{
    private $_dsn     = "mysql:dbname=phpframe-test;host=localhost";
    private $_db_user = "phpframe-test";
    private $_db_pass = "ZfHZtpTXhwYVc8p4";
    private $_db;
    
    public function setUp()
    {
        $this->_db = PHPFrame_Database::getInstance(
            $this->_dsn,
            $this->_db_user,
            $this->_db_pass
        );
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_createTable()
    {
        // Build table object
        $table = new PHPFrame_DatabaseTable($this->_db, "tbl_1");
        $table->addColumn(new PHPFrame_DatabaseColumn(array(
            "name"    => "id", 
            "type"    => PHPFrame_DatabaseColumn::TYPE_INT, 
            "key"     => PHPFrame_DatabaseColumn::KEY_PRIMARY, 
            "extra"   => PHPFrame_DatabaseColumn::EXTRA_AUTOINCREMENT, 
            "null"    => false, 
            "default" => null
        )));
        $table->addColumn(new PHPFrame_DatabaseColumn(array(
            "name"    => "name", 
            "type"    => PHPFrame_DatabaseColumn::TYPE_VARCHAR, 
            "null"    => false, 
            "default" => null
        )));
        
        // Drop the table if it exists
        if ($this->_db->hasTable($table->getName())) {
            $this->_db->dropTable($table->getName());
        }
        
        // Create the db table
        $this->_db->createTable($table);
        
        $this->assertTrue($this->_db->hasTable($table->getName()));
    }
    
    public function test_getTables()
    {
        $tables = $this->_db->getTables();
        $this->assertEquals(1, count($tables));
        
        foreach ($tables as $table) {
            $cols = $table->getColumns();
            $this->assertEquals(2, count($cols));
            
            $this->assertType("PHPFrame_DatabaseColumn", $cols[0]);
            $this->assertType("PHPFrame_DatabaseColumn", $cols[1]);
            
            $this->assertEquals("id", $cols[0]->getName());
            $this->assertEquals(PHPFrame_DatabaseColumn::TYPE_INT, $cols[0]->getType());
            $this->assertEquals(PHPFrame_DatabaseColumn::KEY_PRIMARY, $cols[0]->getKey());
            $this->assertEquals(PHPFrame_DatabaseColumn::EXTRA_AUTOINCREMENT, $cols[0]->getExtra());
            $this->assertEquals(false, $cols[0]->getNull());
            $this->assertEquals(null, $cols[0]->getDefault());
            
            $this->assertEquals("name", $cols[1]->getName());
            $this->assertEquals(PHPFrame_DatabaseColumn::TYPE_VARCHAR, $cols[1]->getType());
            $this->assertEquals(null, $cols[1]->getKey());
            $this->assertEquals(null, $cols[1]->getExtra());
            $this->assertEquals(false, $cols[1]->getNull());
            $this->assertEquals(null, $cols[1]->getDefault());
        }
    }
}
