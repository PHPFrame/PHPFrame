<?php
class DeployController extends PHPFrame_MVC_ActionController
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
    
    public function install($app_name)
    {
        $app_name = (string) trim($app_name);
        
        try {
            // Get model and pass install dir to constructor
            $model = $this->getModel("deploy", array($this->_install_dir));
            
            // Install new app
            $model->install(array("APPNAME"=>$app_name));
            
            $msg = "App created successfully";
            $this->sysevents->setSummary($msg, "success");
            
        } catch (Exception $e) {
            $msg = "Error creating app";
            $this->sysevents->setSummary($msg, "error");
            $this->sysevents->addEventLog($e->getMessage(), "error");
        }
        
        $view = $this->getView("deploy");
        $view->display();
    }
    
    public function update()
    {
        try {
            $model = $this->getModel("deploy", array($this->_install_dir));
            $model->update();
            
            $msg = "App updated successfully";
            $this->sysevents->setSummary($msg, "success");
            
        } catch (Exception $e) {
            $msg = "Error updating app";
            $this->sysevents->setSummary($msg, "error");
            $this->sysevents->addEventLog($e->getMessage(), "error");
        }
        
        $view = $this->getView("deploy");
        $view->display();
    }
    
    public function remove()
    {
        try {
            $model = $this->getModel("deploy", array($this->_install_dir));
            $model->remove();
            
            $msg = "App removed successfully";
            $this->sysevents->setSummary($msg, "success");
            
        } catch (Exception $e) {
            $msg = "Error removing app";
            $this->sysevents->setSummary($msg, "error");
            $this->sysevents->addEventLog($e->getMessage(), "error");
        }
        
        $view = $this->getView("deploy");
        $view->display();
    }
    
    public function upgrade()
    {
        //...
    }
}
