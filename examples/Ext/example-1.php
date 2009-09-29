<?php
require "PHPFrame.php";

$ext = new PHPFrame_FeatureInfo();
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
$ext->setDependencies(array(
    "required" => array(),
    "optional" => array(
        "feature" => array(
            array(
                "name"    => "Login",
                "channel" => "dist.phpframe.org",
                "min"     => "0.0.1",
                "max"     => "0.0.1"
            ),
            array(
                "name"    => "Admin",
                "channel" => "dist.phpframe.org",
                "min"     => "0.0.1",
                "max"     => "0.0.1"
            ),
            array(
                "name"    => "Dashboard",
                "channel" => "dist.phpframe.org",
                "min"     => "0.0.1",
                "max"     => "0.0.1"
            )
        )
    )
));
$ext->addContent(array("path"=>"src/controllers/users.php", "role"=>"php"));
$ext->addContent(array("path"=>"src/controllers/users.php", "role"=>"php"));
$ext->addContent(array("path"=>"src/models/users.php", "role"=>"php"));

$mapper = new PHPFrame_Mapper(
    "PHPFrame_FeatureInfo", 
    "features",
    PHPFrame_Mapper::STORAGE_XML,
    false,
    "/Users/lupomontero/Desktop" 
);

$mapper->insert($ext);
print_r($ext);
