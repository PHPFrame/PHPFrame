<?php
include_once "PHPFrame.php";

$array_obj = new PHPFrame_Array(array(1,2,3));
$assoc_array_obj = new PHPFrame_Array(array("first"=>1,"second"=>2,3, "array"=>array(1,2,3)));

print_r($array_obj);
print_r($assoc_array_obj);

if ($array_obj->isAssoc()) {
    echo "\$array_obj is an associative array\n";
} else {
    echo "\$array_obj is NOT an associative array\n";
}

if ($assoc_array_obj->isAssoc()) {
    echo "\$assoc_array_obj is an associative array\n";
} else {
    echo "\$assoc_array_obj is NOT an associative array\n";
}


echo "\$array_obj has a depth of ".$array_obj->depth()."\n";
echo "\$assoc_array_obj has a depth of ".$assoc_array_obj->depth()."\n";
