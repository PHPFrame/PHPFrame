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
    /**
     * Constructor.
     * 
     * @return void
     * @since  1.0
     */
    public function __construct()
    {
        parent::__construct("create");
    }
    
    /**
     * Create a new application
     * 
     * @param string $app_name            The name of the new application.
     * @param string $template            [Optional] The application template 
     *                                    to use. The default value is "basic".
     * @param string $allow_non_empty_dir [Optional] Whether to allow 
     *                                    installation in a directory that is 
     *                                    not empty. The default value is FALSE.
     * @param string $install_dir         [Optional] Absolute path to directory
     *                                    where we want to create the new app.
     * 
     * @return void
     * @since  1.0
     */
    public function create(
        $app_name, 
        $template="basic", 
        $allow_non_empty_dir=false,
        $install_dir=null
    ) {
        $app_name            = trim((string) $app_name);
        $allow_non_empty_dir = (bool) $allow_non_empty_dir;
        
        if (!in_array($template, array("basic"))) {
            $msg = "Unknown app template '".$template."'.";
            $this->raiseError($msg);
            return;
        }
        
        if (is_null($install_dir)) {
            $install_dir = getcwd();
        }
        
        try {
            // Get model and pass install dir to constructor
            $model = new AppTemplate(
                $install_dir, 
                $this->config()->get("sources.preferred_mirror"),
                $this->config()->get("sources.preferred_state")
            );
            
            // Install new app
            $model->install($app_name, $template, $allow_non_empty_dir);
            
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
     * @param string $install_dir [Optional] Absolute path to directory where 
     *                            we want to create the new app.
     * 
     * @return void
     * @since  1.0
     * @todo   Have to check for databases and other things to delete.
     */
    public function remove($install_dir=null)
    {
        if (is_null($install_dir)) {
            $install_dir = getcwd();
        }
        
        if (!is_dir($install_dir) || !is_writable($install_dir)) {
            $msg  = "Could not delete directory '".$install_dir."'. Please ";
            $msg .= "check that the directory exists and that you have ";
            $msg .= "write permissions.";
            $this->raiseError($msg);
            return;
        }
        
        try {
            $model = new AppTemplate($install_dir);
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
