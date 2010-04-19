<?php
require "PHPFrame.php";

// Read config file in current directory
$config = new PHPFrame_Config("phpframe.ini");

// print config object as string
// Note that if we try to use a config object as a string it will automatically
// be cast to a string representing the ini file
echo '<pre>'.$config.'</pre>';