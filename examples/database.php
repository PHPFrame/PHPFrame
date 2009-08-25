<?php
function loadFramework()
{   
    /**
     * Installation constants
     */
    define('PHPFRAME_INSTALL_DIR', str_replace(DIRECTORY_SEPARATOR."examples", "", dirname(__FILE__)));
    define("PHPFRAME_CONFIG_DIR", PHPFRAME_INSTALL_DIR.DIRECTORY_SEPARATOR."etc");
    define("PHPFRAME_TMP_DIR", PHPFRAME_INSTALL_DIR.DIRECTORY_SEPARATOR."tmp");
    define("PHPFRAME_VAR_DIR", PHPFRAME_INSTALL_DIR.DIRECTORY_SEPARATOR."var");
    
    // Include PHPFrame main file
    require_once "PHPFrame.php";
}


loadFramework();

?>
<h1>PHPFrame Mapper Subpackage Examples</h1>

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