<?php
class DummyController extends PHPFrame_MVC_ActionController
{
    public function __construct()
    {
        parent::__construct("index");
    }
    
    public function index()
    {
        echo "<h1>Hello PHPFrame</h1>";
        echo "This is the Default Action in the Default Controller <br />";
        echo "You can set the Default Controller in etc/config.xml <br />";
        echo "Feel free to replace me, I am just an example.";
    }
}
