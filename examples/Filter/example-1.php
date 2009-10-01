<?php
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$path_array = array_splice($path_array, 0, (count($path_array)-2));
$PHPFrame   = implode(DIRECTORY_SEPARATOR, $path_array).DIRECTORY_SEPARATOR;
$PHPFrame  .= "src".DIRECTORY_SEPARATOR."PHPFrame.php";
require_once $PHPFrame;

//$filter_options = array("min_length"=>6, "max_length"=>10);
//$filter         = new PHPFrame_StringFilter($filter_options);
//$sanitiser      = new PHPFrame_Sanitiser();
$validator      = new PHPFrame_Validator();

$validator->throwExceptions(false);
//$validator->attachFilter($filter);
//$validator->attachSanitiser($sanitiser);

//var_dump($validator->isValid("too long a string"));
//var_dump($validator->getOriginalValue());
//var_dump($validator->getSanitisedValue());
//var_dump($validator->getFilteredValue());

echo "<pre>";
print_r($validator);
