<?php
/**
 * data/CLITool/src/controllers/app.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   PHPFrame_CLITool
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * App controller.
 * 
 * @category PHPFrame
 * @package  PHPFrame_CLITool
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class AppController extends PHPFrame_ActionController
{
    private $_install_dir=null;
    
    /**
     * Constructor
     * 
     * @param string $install_dir
     * 
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
    
    /**
     * Create a new application
     * 
     * @param string $app_name
     * @param string $db_driver
     * @param string $db_name
     * @param string $db_host
     * @param string $db_user
     * @param string $db_pass
     * @param string $template
     * @param string $allow_non_empty_dir
     * 
     * @return void
     * @since  1.0
     */
    public function new_app(
        $app_name, 
        $db_driver="SQLite",
        $db_name="data.db",
        $db_host=null,
        $db_user=null,
        $db_pass=null,
        $template=null, 
        $allow_non_empty_dir=false
    ) {
        $app_name            = trim((string) $app_name);
        $allow_non_empty_dir = (bool) $allow_non_empty_dir;
        
        try {
            // Get model and pass install dir to constructor
            $model = new AppTemplate(
                $this->_install_dir, 
                $this->config()->get("sources.preferred_mirror"),
                $this->config()->get("sources.preferred_state")
            );
            
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
    }
    
    /**
     * Remove application.
     * 
     * @return void
     * @since  1.0
     * @todo   Have to check for databases and other things to delete.
     */
    public function remove()
    {
        try {
            $model = new AppTemplate($this->_install_dir);
            $model->remove();
            
            $msg = "App removed successfully";
            $this->notifySuccess($msg);
            
        } catch (Exception $e) {
            $msg = "Error removing app";
            $this->raiseError($msg);
            $this->raiseError($e->getMessage());
        }
    }
}
