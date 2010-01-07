<?php
require "PHPFrame.php";

// Read config file in current directory
$config = new PHPFrame_Config("phpframe.ini");

// Set some config keys
$config->set("app_name", "New app name");
$config->set("debug.display_exceptions", true);
$config->set("debug.log_level", 3);

// Lets prove that the data was updated
echo 'The new name of our app is: '.$config->get("app_name");