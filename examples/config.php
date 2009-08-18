<?php
//Hack to override path to PHPFrame source
$PHPFrame_path = "/Users/lupomontero/Documents/workspace/PHPFrame/src";
set_include_path($PHPFrame_path . PATH_SEPARATOR . get_include_path());
require "PHPFrame.php";

//$config_file = "/Users/lupomontero/Documents/workspace/PHPFrame/data/etc/groups.xml";
//$config = PHPFrame_Config::instance($config_file);
//var_dump($config);
//
//$config_file2 = "/Users/lupomontero/Documents/workspace/PHPFrame/data/etc/lib.xml";
//$config2 = PHPFrame_Config::instance($config_file2);
//
//$config3 = PHPFrame::Config();
//
//var_dump($config2, $config3);

$plugin = new PHPFrame_Addons_Plugin();
$mapper = new PHPFrame_Mapper("PHPFrame_Addons_Plugin", "plugins");

$mapper->insert($plugin);

var_dump($plugin, $mapper);
