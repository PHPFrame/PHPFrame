<?php
class VersionController extends PHPFrame_MVC_ActionController
{
    public function __construct()
    {
        parent::__construct("index");
    }
    
    public function index()
    {
        $view = $this->getView("version");
        $view->addData("version", PHPFrame::Version());
        $view->display();
    }
}
