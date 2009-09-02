<?php
require "PHPFrame.php";

// Get default config
$config = PHPFrame::Config();

// print config object as string
// Note that if we try to use a config object as a string it will automatically 
// be cast to a string representing the ini file 
echo '<h2>The config object as a string</h2>';
echo '<pre>'.$config.'</pre>';

// Now lets see what keys are abailablein current config object
echo '<h2>The keys available in this config object</h2>';
var_dump($config->getKeys());

// Set some config keys
$config->set("app_name", "New app name");
$config->set("debug.enable", true);
$config->set("debug.log_level", 3);

// Lets prove that the data was updated
echo 'The new name of our app is: ';
echo $config->get("app_name");
