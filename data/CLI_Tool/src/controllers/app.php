<?php
class AppController extends PHPFrame_ActionController
{
    private $_install_dir=null;
    
    public function __construct($install_dir=null)
    {
        if (is_null($install_dir)) {
            $this->_install_dir = getcwd();
        } else {
            $this->_install_dir = (string) trim($install_dir);
        }
        
        parent::__construct("new_app");
    }
    
    public function new_app($app_name, $template=null, $allow_non_empty_dir=false)
    {
        $app_name = (string) trim($app_name);
        $allow_non_empty_dir = (bool) trim($allow_non_empty_dir);
        
        try {
            // Get model and pass install dir to constructor
            $model = $this->getModel("AppTemplate", array($this->_install_dir));
            
            // Install new app
            $model->install(
                array("app_name"=>$app_name), 
                $template, 
                $allow_non_empty_dir
            );
            
            $msg = "App created successfully";
            $this->notifySuccess($msg);
            
        } catch (Exception $e) {
            $msg = "Could NOT create new app";
            $this->raiseError($msg);
            $this->raiseError($e->getMessage());
        }
        
        $this->getView()->display();
    }
    
    public function update()
    {
        try {
            $model = $this->getModel("AppTemplate", array($this->_install_dir));
            $model->update();
            
            $msg = "App updated successfully";
            $this->notifySuccess($msg);
            
        } catch (Exception $e) {
            $msg = "Error updating app";
            $this->raiseError($msg);
            $this->raiseError($e->getMessage());
        }
        
        $this->getView()->display();
    }
    
    public function remove()
    {
        try {
            $model = $this->getModel("AppTemplate", array($this->_install_dir));
            $model->remove();
            
            $msg = "App removed successfully";
            $this->notifySuccess($msg);
            
        } catch (Exception $e) {
            $msg = "Error removing app";
            $this->raiseError($msg);
            $this->raiseError($e->getMessage());
        }
        
        $this->getView()->display();
    }
}
