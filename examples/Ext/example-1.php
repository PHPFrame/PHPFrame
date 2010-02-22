<?php
require "PHPFrame.php";

$ext = new PHPFrame_PluginInfo();
$ext->setName("Users");
$ext->setChannel("dist.phpframe.org");
$ext->setSummary("This is the summary");
$ext->setDescription("This is the description");
$ext->setAuthor("Luis Montero");
$ext->setDate("2009-08-27");
$ext->setTime("00:47");
$ext->setVersion(array("release"=>"0.1.1", "api"=>"1.0"));
$ext->setStability(array("release"=>"beta", "api"=>"beta"));
$ext->setLicense(array(
    "name" => "BSD Style", 
    "uri"  => "http://www.opensource.org/licenses/bsd-license.php"
));
$ext->setNotes("This are the notes....");

$mapper = new PHPFrame_Mapper(
    "PHPFrame_PluginInfo",
    dirname(__FILE__),  
    "features"
);

$mapper->insert($ext);
print_r($ext);
