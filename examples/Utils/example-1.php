<?php
require "PHPFrame.php";

$array = array(1, 2, 3);
print_r($array);
$serialised = PHPFrame_XMLSerialiser::serialise($array);
echo $serialised;
$unserialised = PHPFrame_XMLSerialiser::unserialise($serialised);
print_r($unserialised);



$array = array("first"=>1,"second"=>2,"third"=>3);
print_r($array);
$serialised = PHPFrame_XMLSerialiser::serialise($array);
echo $serialised;
$unserialised = PHPFrame_XMLSerialiser::unserialise($serialised);
print_r($unserialised);



$array = array(array(1,2,3), array(4,5,6));
print_r($array);
$serialised = PHPFrame_XMLSerialiser::serialise($array, "root");
echo $serialised;
$unserialised = PHPFrame_XMLSerialiser::unserialise($serialised);
print_r($unserialised);



$array = array(array("first"=>1,"second"=>2,"third"=>3), "a value");
print_r($array);
$serialised = PHPFrame_XMLSerialiser::serialise($array);
echo $serialised;
$unserialised = PHPFrame_XMLSerialiser::unserialise($serialised);
print_r($unserialised);



$array = array(array(
    "first"=>1,
    "second"=>array(1,2,3),
    "dependencies"=>array(
        "required"=>array(),
        "optional"=>array(
            array(
                "name" => "login",
                "version" => 1.0
            ),
            array(
                "name" => "admin",
                "version" => 1.0
            )
        )
    )
));
print_r($array);
$serialised = PHPFrame_XMLSerialiser::serialise($array);
echo $serialised;
$unserialised = PHPFrame_XMLSerialiser::unserialise($serialised);
print_r($unserialised);

