<?php
/**
 * data/CLITool/src/controllers/config.php
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
 * Configuration controller.
 *
 * @category PHPFrame
 * @package  PHPFrame_CLITool
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class ConfigController extends PHPFrame_ActionController
{
    /**
     * Constructor
     *
     * @param PHPFrame_Application $app Reference to application object.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Application $app)
    {
        parent::__construct($app, "show");
    }

    /**
     * List all configuration values.
     *
     * @param string $install_dir [Optional] Absolute path to app for which to
     *                            get the config obj.
     *
     * @return void
     * @since  1.0
     */
    public function show($install_dir=null)
    {
        if (is_null($install_dir)) {
            $install_dir = getcwd();
        }

        $config = $this->_getConfigObj($install_dir);

        $this->response()->body((string) $config);
    }

    /**
     * Display value for a given configuration parameter.
     *
     * @param string $key         The name of the config parameter.
     * @param string $install_dir [Optional] Absolute path to app for which to
     *                            get the config obj.
     *
     * @return void
     * @since  1.0
     */
    public function get($key, $install_dir=null)
    {
        if (is_null($install_dir)) {
            $install_dir = getcwd();
        }

        $key = trim((string) $key);

        $config = $this->_getConfigObj($install_dir);

        $this->response()->body($key.": ".$config->get($key));
    }

    /**
     * Set the value of a given configuration parameter.
     *
     * @param string $key         The name of the config parameter.
     * @param string $value       The new value for the parameter.
     * @param string $install_dir [Optional] Absolute path to app for which to
     *                            get the config obj.
     *
     * @return void
     * @since  1.0
     */
    public function set($key, $value, $install_dir=null)
    {
        if (is_null($install_dir)) {
            $install_dir = getcwd();
        }

        $key    = trim((string) $key);
        $value  = trim((string) $value);
        $config = $this->_getConfigObj($install_dir);

        try {
            $config->set($key, $value);
            $config->store();

            $this->notifySuccess("Config param updated.");

        } catch (Exception $e) {
            $this->raiseError("An error ocurred while saving config.");
        }

        $this->response()->body($key.": ".$config->get($key));
    }

    /**
     * Get config object.
     *
     * @param string $install_dir Absolute path to app for which to get the
     *                            config obj.
     *
     * @return PHPFrame_Config
     * @throws RuntimeException
     * @since  1.0
     */
    private function _getConfigObj($install_dir)
    {
        $path = $install_dir.DS."etc".DS."phpframe.ini";

        if (!is_file($path)) {
            $msg = "Cannot load config File";
            throw new RuntimeException($msg);
        }

        return new PHPFrame_Config($path);
    }
}
