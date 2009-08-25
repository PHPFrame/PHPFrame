<?php
require "PHPFrame.php";

define("PHPFRAME_VAR_DIR", dirname(__FILE__));
define("PHPFRAME_TMP_DIR", dirname(__FILE__));

// Get default config
$config = PHPFrame::Config();

// Dump the sections and keys in current config object
var_dump($config->getSections());
var_dump($config->getKeys());

// Set some config keys
$config->set("app_name", "UPDATED APP NAME");
$config->set("debug.enable", true);
$config->set("debug.log_level", 3);


// echo config object as ini string
echo $config;

// Dump config object
var_dump($config);
