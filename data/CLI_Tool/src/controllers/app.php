<?php
class AppController extends PHPFrame_ActionController
{
    private $_install_dir=null;
    
    /**
     * Constructor
     * 
     * @param string $install_dir
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct($install_dir=null)
    {
        if (is_null($install_dir)) {
            $this->_install_dir = getcwd();
        } else {
            $this->_install_dir = trim((string) $install_dir);
        }
        
        parent::__construct("new_app");
    }
    
    public function new_app(
        $app_name, 
        $db_driver="SQLite",
        $db_name="data.db",
        $db_host=null,
        $db_user=null,
        $db_pass=null,
        $template=null, 
        $allow_non_empty_dir=false
    )
    {
        $app_name            = trim((string) $app_name);
        $allow_non_empty_dir = (bool) $allow_non_empty_dir;
        
        try {
            // Get model and pass install dir to constructor
            $model = $this->getModel("AppTemplate", array($this->_install_dir));
            
            // Install new app
            $model->install(
                array(
                    "app_name"  => $app_name,
                    "db.driver" => $db_driver,
                    "db.name"   => $db_name,
                    "db.host"   => $db_host,
                    "db.user"   => $db_user,
                    "db.pass"   => $db_pass
                ), 
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
