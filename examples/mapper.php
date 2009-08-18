<?php
function loadFramework()
{
    //TODO: This needs to be removed. It is temporarily here to make up for the
    // release of PHPFrame as a PEAR package
    $PHPFrame_path = "/Users/lupomontero/Documents/workspace/PHPFrame/src";
    set_include_path($PHPFrame_path . PATH_SEPARATOR . get_include_path());
    
    include_once "PHPFrame.php";
}

loadFramework();

?>
<h1>PHPFrame Database Subpackage Examples</h1>

<h2>Code</h2>

<pre style="border:1px solid #990000; padding:20px; background-color: #FFFFCC;">

</pre>

<?php
$plugin = new PHPFrame_Addons_Plugin();
echo '<h2>Object to be inserted</h2>';
var_dump($plugin);

$mapper = new PHPFrame_Mapper(
    "PHPFrame_Addons_Plugin", 
    null, 
    PHPFrame_Mapper::STORAGE_XML, 
    false, 
    "/Users/lupomontero/Desktop".DS."domain.objects"
);

// Show objects before insert
echo '<h2>Collection BEFORE insert</h2>';
foreach ($mapper->find() as $item) {
    var_dump($item);
}

// Insert new object
$mapper->insert($plugin);

// Show updated collection
echo '<h2>Collection AFTER insert</h2>';
foreach ($mapper->find() as $item) {
    var_dump($item);
}
