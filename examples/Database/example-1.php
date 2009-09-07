<?php
require_once "PHPFrame.php";

$dsn = new PHPFrame_MySQLDSN("localhost", "test");
$db = PHPFrame::DB($dsn, "root", "Password");
$result = $db->fetchAssocList("SELECT * FROM a_table");
print_r($result);