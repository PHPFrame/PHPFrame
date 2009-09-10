<?php
include_once "PHPFrame.php";

// Instantiate generic mapper for PHPFrame_ACL class 
// and specify XML storage
$mapper = new PHPFrame_Mapper(
    "PHPFrame_ACL", 
    "acl", 
    PHPFrame_Mapper::STORAGE_XML, 
    false, 
    DS."tmp".DS."domain.objects"
);

// Instantiate persistent object
$acl = new PHPFrame_ACL(array(
    "groupid"=>1, 
    "controller"=>"dummy", 
    "action"=>"*", 
    "value"=>"all"
));

// Insert new object
$mapper->insert($acl);

// Find objects and iterate through collection
foreach ($mapper->find() as $item) {
    print_r($item);
}