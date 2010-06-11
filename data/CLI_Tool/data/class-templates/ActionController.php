<?php
class MyActionController extends PHPFrame_ActionController
{
    public function __construct(PHPFrame_Application $app)
    {
        parent::__construct($app, "index");
    }

    public function index()
    {
        // Do something...
    }
}
