<?php
require "PHPFrame.php";

$db = PHPFrame::DB();
$result = $db->fetchAssocList("SELECT * FROM a_table");
print_r($result);