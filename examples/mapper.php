<h1>PHPFrame Mapper Subpackage Examples</h1>

<h2>Code</h2>

<pre style="border:1px solid #990000; padding:20px; background-color: #FFFFCC;">

</pre>

<?php
include_once "PHPFrame.php";

// Instantiate generic mapper for PHPFrame_Application_ACL class 
// and specify XML storage
$mapper = new PHPFrame_Mapper(
    "PHPFrame_Application_ACL", 
    "acl", 
    PHPFrame_Mapper::STORAGE_XML, 
    false, 
    DS."tmp".DS."domain.objects"
);

// Instantiate domain object
$acl = new PHPFrame_Application_ACL(array(
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
