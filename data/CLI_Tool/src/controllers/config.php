<?php
class ConfigController extends PHPFrame_ActionController
{
    private $_config=null;
    
    public function __construct()
    {
        $path = getcwd().DS."etc".DS."phpframe.ini";
        if (!is_file($path)) {
            $msg = "Cannot load config File";
            throw new RuntimeException($msg);
        }
        
        $this->_config = PHPFrame_Config::instance($path);
        
        parent::__construct("list_all");
    }
    
    public function list_all()
    {
        $str = (string) $this->_config;
        
        $view = $this->getView();
        $view->addData("config", $str);
        $view->display();
    }
    
    public function get($key)
    {
        $key = trim((string) $key);
        
        $view = $this->getView();
        $view->addData($key, $this->_config->get($key));
        $view->display();
    }
    
    public function set($key, $value)
    {
        $key   = trim((string) $key);
        $value = trim((string) $value);
        
        try {
            $this->_config->set($key, $value);
            $this->_config->store();
            
            $this->notifySuccess("Config param updated");
        } catch (Exception $e) {
            $this->raiseError("An error ocurred while saving config");
        }
        
        $view = $this->getView();
        $view->addData($key, $this->_config->get($key));
        $view->display();
    }
}
