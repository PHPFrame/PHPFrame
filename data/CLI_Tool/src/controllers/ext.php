<?php
class ExtController extends PHPFrame_MVC_ActionController
{
    private $_install_dir=null;
    
    public function __construct($install_dir=null)
    {
        if (is_null($install_dir)) {
            $this->_install_dir = getcwd();
        } else {
            $this->_install_dir = (string) trim($install_dir);
        }
        
        parent::__construct("install");
    }
    
    public function install()
    {
        
    }
    
    public function upgrade()
    {
        
    }
    
    public function remove()
    {
        
    }
    
    public function list_installed()
    {
        
    }
    
    public function list_available()
    {
        
    }
}
