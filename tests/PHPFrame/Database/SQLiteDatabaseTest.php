<?php
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$path_array = array_splice($path_array, 0, (count($path_array)-3));
$PHPFrame   = implode(DIRECTORY_SEPARATOR, $path_array).DIRECTORY_SEPARATOR;
$PHPFrame  .= "src".DIRECTORY_SEPARATOR."PHPFrame.php";
require_once $PHPFrame;

class PHPFrame_SQLiteDatabaseTest extends PHPUnit_Framework_TestCase
{
    private $_db;
    
    public function setUp()
    {
        $dsn       = "sqlite:".dirname(__FILE__).DS."data.db";
        $this->_db = PHPFrame_Database::getInstance($dsn);
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_createTable()
    {
        $table = new PHPFrame_DatabaseTable($this->_db, "tbl_1");
        $this->_db->dropTable($table);
        
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
        
        
        $this->_db->createTable($table);
        
        $table = new PHPFrame_DatabaseTable($this->_db, "tbl_1");
        print_r(iterator_to_array($table->getColumns()));
    }
    
    public function test_getTables()
    {
        foreach ($this->_db->getTables() as $table) {
            print_r(iterator_to_array($table->getColumns()));
        }
    }
}
