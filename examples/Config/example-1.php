<?php
require "PHPFrame.php";

// Get default config
$config = PHPFrame::Config();

// print config object as string
// Note that if we try to use a config object as a string it will automatically 
// be cast to a string representing the ini file 
echo '<pre>'.$config.'</pre>';