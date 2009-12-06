<?php
/**
 * data/CLITool/src/controllers/config.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   PHPFrame_CLITool
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * Configuration controller.
 * 
 * @category PHPFrame
 * @package  PHPFrame_CLITool
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since    1.0
 */
class ConfigController extends PHPFrame_ActionController
{
	/**
	 * Reference to config object.
	 * 
	 * @var PHPFrame_Config
	 */
    private $_config=null;
    
    /**
     * Constructor
     * 
     * @return void
     * @since  1.0
     */
    public function __construct()
    {
        $path = getcwd().DS."etc".DS."phpframe.ini";
        if (!is_file($path)) {
            $msg = "Cannot load config File";
            throw new RuntimeException($msg);
        }
        
        $this->_config = new PHPFrame_Config($path);
        
        parent::__construct("list_all");
    }
    
    /**
     * List all configuration values.
     * 
     * @return void
     * @since  1.0
     */
    public function list_all()
    {
        $str = (string) $this->_config;
        
        $view = $this->getView();
        $view->addData("config", $str);
        
        $this->response()->setBody($view);
    }
    
    /**
     * Display value for a given configuration parameter.
     * 
     * @param string $key The name of the config parameter.
     * 
     * @return void
     * @since  1.0
     */
    public function get($key)
    {
        $key = trim((string) $key);
        
        $view = $this->getView();
        $view->addData($key, $this->_config->get($key));
        
        $this->response()->setBody($view);
    }
    
    /**
     * Set the value of a given configuration parameter.
     * 
     * @param string $key   The name of the config parameter.
     * @param string $value The new value for the parameter.
     * 
     * @return void
     * @since  1.0
     */
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
        
        $this->response()->setBody($view);
    }
}
