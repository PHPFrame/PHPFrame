<?php
function loadFramework()
{
    //TODO: This needs to be removed. It is temporarily here to make up for the
    // release of PHPFrame as a PEAR package
    $PHPFrame_path = "/Users/lupomontero/Documents/workspace/PHPFrame/src";
    set_include_path(get_include_path() . PATH_SEPARATOR . $PHPFrame_path);
    
    /**
     * Set convenience DS constant (directory separator depends on server operating system).
     */
    define( 'DS', DIRECTORY_SEPARATOR );
    
    include_once "config.php";
    include_once "PHPFrame.php";
}


loadFramework();

?>
<h1>PHPFrame Database Subpackage Examples</h1>

<h2>Code</h2>

<pre style="border:1px solid #990000; padding:20px; background-color: #FFFFCC;">
$row = new PHPFrame_Database_Row("#__users");

$id_obj = new PHPFrame_Database_IdObject();
$id_obj->select(array("u.*", "g.name AS groupname"))
       ->from("#__users AS u")
       ->join("JOIN #__groups g ON g.id = u.groupid");

$row->load($id_obj);
</pre>
<?php

$row = new PHPFrame_Database_Row("#__users");

$id_obj = new PHPFrame_Database_IdObject();
$id_obj->select(array("u.*", "g.name AS groupname"))
       ->from("#__users AS u")
       ->join("JOIN #__groups g ON g.id = u.groupid");

$row->load($id_obj);

echo '<h3>$id_obj->getSelectFields()</h3>';
var_dump($id_obj->getSelectFields());

echo '<h3>$id_obj->__toString()</h3>';
echo $id_obj;

echo '<h3>$row->getFields()</h3>';
var_dump($row->getFields());

echo '<h3>$row->toArray()</h3>';
var_dump($row->toArray());