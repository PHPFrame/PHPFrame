<?php
class ExtController extends PHPFrame_MVC_ActionController
{
    const TYPE_FEATURE = 0x00000001;
    const TYPE_THEME  = 0x00000002;
    const TYPE_LIB    = 0x00000003;
    
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
    
    public function install($name, $type=self::TYPE_FEATURE)
    {
        
    }
    
    public function upgrade($name, $type=self::TYPE_FEATURE)
    {
        
    }
    
    public function remove($name, $type=self::TYPE_FEATURE)
    {
        
    }
    
    public function list_installed()
    {
        
    }
    
    public function list_available()
    {
        
    }
}
