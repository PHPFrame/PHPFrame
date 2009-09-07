<?php
include_once "PHPFrame.php";

$array_obj = new PHPFrame_Array(array(1,2,3));
$assoc_array_obj = new PHPFrame_Array(array("first"=>1,"second"=>2,3, "array"=>array(1,2,3)));

var_dump($array_obj->isAssoc(), $assoc_array_obj->isAssoc());
var_dump($array_obj->depth(), $assoc_array_obj->depth());

print_r($array_obj);
