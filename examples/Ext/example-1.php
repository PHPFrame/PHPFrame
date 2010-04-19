<?php
require "PHPFrame.php";

$ext = new PHPFrame_PluginInfo();
$ext->name("Users");
$ext->channel("dist.phpframe.org");
$ext->summary("This is the summary");
$ext->description("This is the description");
$ext->author("Luis Montero");
$ext->date("2009-08-27");
$ext->version("0.1.1");
$ext->license("BSD Style");

$mapper = new PHPFrame_Mapper(
    "PHPFrame_PluginInfo",
    dirname(__FILE__),
    "features"
);

$mapper->insert($ext);
print_r($ext);
