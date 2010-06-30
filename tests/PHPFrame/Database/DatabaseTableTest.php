<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_DatabaseTableTest extends PHPUnit_Framework_TestCase
{
    private $_dsn     = "mysql:dbname=phpframe-test;host=localhost";
    private $_db_user = "phpframe-test";
    private $_db_pass = "Passw0rd";
    private $_db;
    private $_table;

    public function setUp()
    {
        $this->_db = PHPFrame_Database::getInstance(
            $this->_dsn,
            $this->_db_user,
            $this->_db_pass
        );

        // Build table object
        $this->_table = new PHPFrame_DatabaseTable($this->_db, "tbl_1");
        $this->_table->addColumn(new PHPFrame_DatabaseColumn(array(
            "name"    => "id",
            "type"    => PHPFrame_DatabaseColumn::TYPE_INT,
            "key"     => PHPFrame_DatabaseColumn::KEY_PRIMARY,
            "extra"   => PHPFrame_DatabaseColumn::EXTRA_AUTOINCREMENT,
            "null"    => false,
            "default" => null
        )));
        $this->_table->addColumn(new PHPFrame_DatabaseColumn(array(
            "name"    => "name",
            "type"    => PHPFrame_DatabaseColumn::TYPE_VARCHAR,
            "null"    => false,
            "default" => null
        )));

        // Drop the table if it exists
        if ($this->_db->hasTable($this->_table->getName())) {
            $this->_db->dropTable($this->_table->getName());
        }

        // Create the db table
        $this->_db->createTable($this->_table);
    }

    public function tearDown()
    {
        //...
    }

    public function test_()
    {
        //...
    }
}
