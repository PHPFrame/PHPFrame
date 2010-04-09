<?php
// @codingStandardsIgnoreFile
class PHPFrame_TestableActionController extends PHPFrame_ActionController
{
    public function __construct()
    {
        parent::__construct("index");
    }

    public function index()
    {
        $this->response()->title("The page title");
        $this->response()->body("Lorem ipsum...");
    }

    public function app()
    {
        return parent::app();
    }

    public function config()
    {
        return parent::config();
    }

    public function request()
    {
        return parent::request();
    }

    public function response()
    {
        return parent::response();
    }

    public function registry()
    {
        return parent::registry();
    }

    public function mailer()
    {
        return parent::mailer();
    }

    public function imap()
    {
        return parent::imap();
    }

    public function logger()
    {
        return parent::logger();
    }

    public function session()
    {
        return parent::session();
    }

    public function user()
    {
        return parent::user();
    }

    public function raiseError($msg)
    {
        parent::raiseError($msg);
    }

    public function raiseWarning($msg)
    {
        parent::raiseWarning($msg);
    }

    public function notifyInfo($msg)
    {
        parent::notifyInfo($msg);
    }

    public function notifySuccess($msg)
    {
        parent::notifySuccess($msg);
    }
}
