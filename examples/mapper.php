<?php
function loadFramework()
{
    //TODO: This needs to be removed. It is temporarily here to make up for the
    // release of PHPFrame as a PEAR package
    $PHPFrame_path = "/Users/lupomontero/Documents/workspace/PHPFrame/src";
    set_include_path(get_include_path() . PATH_SEPARATOR . $PHPFrame_path);
    
    include_once "PHPFrame.php";
}


loadFramework();

?>
<h1>PHPFrame Database Subpackage Examples</h1>

<h2>Code</h2>

<pre style="border:1px solid #990000; padding:20px; background-color: #FFFFCC;">
$factory = new PHPFrame_Mapper_PersistenceFactory("PHPFrame_User");
$assembler = $factory->getAssembler();
$id_obj = $factory->getIdObject();
echo '<h2>Assembler: </h2>';
var_dump($factory, $assembler, $id_obj);

$user = $assembler->findOne(62);
echo '<h2>Found one user: </h2>';
var_dump($user);

$collection = $assembler->find($id_obj);
echo '<h2>Found collection: </h2>';
foreach ($collection as $obj) {
    var_dump($obj);
}

$user2 = clone $user;
echo '<h2>Cloned user: </h2>';
var_dump($user2); 

//$assembler->insert($user2);
//echo '<h2>Cloned user after insert: </h2>';
//var_dump($user2); 


$options = array("groupid"=>2, "email"=>"some@email.com");
$user3 = new PHPFrame_User($options);
echo '<h2>User initialised with options: </h2>';
var_dump($user3->toArray());

$user4 = new PHPFrame_User();
$user4->setGroupId(1);
$user4->setEmail("me@lupomontero.com");
$user4->setUserName("lupo.montero");
$user4->setFirstName("Lupo");
$user4->setLastName("Montero");
$user4->setPhoto("default.png");
$user4->addOpenidUrl("http://www.e-noise.com");

echo '<h2>User properties set using setter methods: </h2>';
var_dump($user4->toArray());
</pre>
<?php

$factory = new PHPFrame_Mapper_PersistenceFactory("PHPFrame_User");
$assembler = $factory->getAssembler();
$id_obj = $factory->getIdObject();
echo '<h2>Assembler: </h2>';
var_dump($factory, $assembler, $id_obj);

$user = $assembler->findOne(62);
echo '<h2>Found one user: </h2>';
var_dump($user);

$collection = $assembler->find($id_obj);
echo '<h2>Found collection: </h2>';
foreach ($collection as $obj) {
    var_dump($obj);
}

$user2 = clone $user;
echo '<h2>Cloned user: </h2>';
var_dump($user2); 

//$assembler->insert($user2);
//echo '<h2>Cloned user after insert: </h2>';
//var_dump($user2); 


$options = array("groupid"=>2, "email"=>"some@email.com");
$user3 = new PHPFrame_User($options);
echo '<h2>User initialised with options: </h2>';
var_dump($user3->toArray());

$user4 = new PHPFrame_User();
$user4->setGroupId(1);
$user4->setEmail("me@lupomontero.com");
$user4->setUserName("lupo.montero");
$user4->setFirstName("Lupo");
$user4->setLastName("Montero");
$user4->setPhoto("default.png");
$user4->addOpenidUrl("http://www.e-noise.com");

echo '<h2>User properties set using setter methods: </h2>';
var_dump($user4->toArray());

exit;
