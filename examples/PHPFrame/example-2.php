<?php
require "PHPFrame.php";

// Set constant with app specific path to tmp directory
define("PHPFRAME_TMP_DIR", dirname(__FILE__).DIRECTORY_SEPARATOR."tmp");
// Initialise PHPFrame environment
PHPFrame::Env();

// Get permissions object and current user's group id from registries
$permissions = PHPFrame::AppRegistry()->getPermissions();
$group_id    = PHPFrame::Session()->getGroupId();

// Check whether current user is allowed to call an action called 'save' in a 
// controller called 'content'
if ($permissions->authorise("content", "save", $group_id, true)) {
    echo "You are allowed to call action 'save' in controller 'content'!";
} else {
    echo "Sorry, you can not save content.";
}