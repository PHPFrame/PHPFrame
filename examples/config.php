<?php
//Hack to override path to PHPFrame source
$PHPFrame_path = "/Users/lupomontero/Documents/workspace/PHPFrame/src";
set_include_path($PHPFrame_path . PATH_SEPARATOR . get_include_path());
require "PHPFrame.php";

define("PHPFRAME_VAR_DIR", dirname(__FILE__));
define("PHPFRAME_TMP_DIR", dirname(__FILE__));

$config_file = "/Users/lupomontero/Documents/workspace/PHPFrame/data/etc/groups.xml";
$config = PHPFrame_Config::instance($config_file);
var_dump($config);

$config2 = PHPFrame::Config();

var_dump($config2);
