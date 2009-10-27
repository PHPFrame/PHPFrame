<?php
require_once "PHPFrame.php";

$db = PHPFrame::DB("sqlite:".dirname(__FILE__).DS."data.sql3");
//$db->query("DROP TABLE t1");
//$db->query("create table t1 (t1key INTEGER PRIMARY KEY,data TEXT,num double,timeEnter DATE)");
//$db->query("insert into t1 (data,num) values ('This is sample data',3)");
//$db->query("insert into t1 (data,num) values ('More sample data',6)");
//$db->query("insert into t1 (data,num) values ('And a little more',9)");
var_dump($db->fetchAssocList("select * from t1 limit 2"));
exit;
print_r($db->getTables());
exit;

$result = $db->fetchAssocList("SELECT * FROM a_table");

print_r($result);