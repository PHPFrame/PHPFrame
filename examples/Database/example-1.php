<?php
require_once "PHPFrame.php";

$db = PHPFrame_Database::getInstance("sqlite:".dirname(__FILE__).DS."data.sql3");

if ($db->hasTable("t1")) {
    $db->dropTable("t1");
}

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

print_r($table->getColumns());

$db->createTable($table);

$db->query("INSERT INTO t1 (name) VALUES ('This is sample data')");
$db->query("INSERT INTO t1 (name) VALUES ('More sample data')");
$db->query("INSERT INTO t1 (name) VALUES ('And a little more')");

print_r($db->fetchAssocList("SELECT * FROM t1"));
