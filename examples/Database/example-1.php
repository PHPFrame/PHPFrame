<?php
require_once "PHPFrame.php";

// Get instance of database object, if SQLite database doesn't exist it will be
// created automatically
$db = PHPFrame_Database::getInstance("sqlite:".dirname(__FILE__).DS."data.sql3");

// If the database already has a table called "t1" we drop it
if ($db->hasTable("t1")) {
    $db->dropTable("t1");
}

// Create new table object and add two columns to it
$table = new PHPFrame_DatabaseTable($db, "t1");
$table->addColumn(new PHPFrame_DatabaseColumn(array(
    "name"    => "id",
    "type"    => PHPFrame_DatabaseColumn::TYPE_INT,
    "null"    => false,
    "key"     => PHPFrame_DatabaseColumn::KEY_PRIMARY,
    "default" => null,
    "extra"   => PHPFrame_DatabaseColumn::EXTRA_AUTOINCREMENT
)));
$table->addColumn(new PHPFrame_DatabaseColumn(array(
    "name"    => "name",
    "type"    => PHPFrame_DatabaseColumn::TYPE_VARCHAR,
    "null"    => false,
    "default" => null
)));

// Show the table columns we just added to the table object
print_r($table->getColumns());

// Create the table in the database
$db->createTable($table);

// Now we add some data to prove that our new table was created
$db->query("INSERT INTO t1 (name) VALUES ('This is sample data')");
$db->query("INSERT INTO t1 (name) VALUES ('More sample data')");
$db->query("INSERT INTO t1 (name) VALUES ('And a little more')");

// Query data from the new table and print it
print_r($db->fetchAssocList("SELECT * FROM t1"));
