<?php
class ExtController extends PHPFrame_ActionController
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
    
    public function install($name, $type=null)
    {
        $ext_installer = $this->getModel("ExtInstaller", array($this->_install_dir));
        
        try {
            $ext_installer->install($name, $type);
            
        } catch (Exception $e) {
            $this->sysevents->setSummary($e->getMessage());
        }
        
        $this->getView()->display();
    }
    
    public function upgrade($name, $type=self::EXT_FEATURE)
    {
        echo "This should upgrade a given extension";
    }
    
    public function remove($name, $type=self::EXT_FEATURE)
    {
        echo "This should remove a given extension";
    }
    
    public function list_installed()
    {
        echo "This should show a list of installed extensions";
    }
    
    public function list_available()
    {
        echo "This should show a list of available extensions";
    }
}
