<?php
include_once "PHPFrame.php";

$user = new PHPFrame_User(array("username"=>"lupo"));

print_r(iterator_to_array($user));