<?php
require "PHPFrame.php";

// Get default config
$config = PHPFrame::Config();

// Set some config keys
$config->set("app_name", "New app name");
$config->set("debug.enable", true);
$config->set("debug.log_level", 3);

// Lets prove that the data was updated
echo 'The new name of our app is: '.$config->get("app_name");