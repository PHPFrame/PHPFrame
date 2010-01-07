<?php
require "PHPFrame.php";

// Read config file in current directory
$config = new PHPFrame_Config("phpframe.ini");

print_r($config->getKeys());