<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_SQLiteDatabaseTest extends PHPUnit_Framework_TestCase
{
    private $_db_file, $_db;

    public function setUp()
    {
        $this->_db_file = dirname(__FILE__).DS."data.db";
        $dsn            = "sqlite:".$this->_db_file;
        $this->_db      = PHPFrame_Database::getInstance($dsn);
    }

    public function tearDown()
    {
        // Delete database file
        if (is_file($this->_db_file)) {
            unlink($this->_db_file);
        }
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

    public function test_serialise()
    {
        $serialised = serialize($this->_db);
        $unserialised = unserialize($serialised);

        // Reconnect original instance because serialisation will disconnect it
        $this->_db->connect();

        $this->assertEquals($this->_db, $unserialised);
    }
}
