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
     * Constructor.
     * 
     * @param string $install_dir [Optional] Absolute path to installation 
     *                            directory. If not passed the current working 
     *                            directory is used.
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
     * @param string $app_name            The name of the new application.
     * @param string $template            [Optional] The application template 
     *                                    to use. The default value is "basic".
     * @param string $allow_non_empty_dir [Optional] Whether to allow 
     *                                    installation in a directory that is 
     *                                    not empty. The default value is FALSE.
     * 
     * @return void
     * @since  1.0
     */
    public function new_app(
        $app_name, 
        $template="basic", 
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
