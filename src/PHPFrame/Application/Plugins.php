<?php
/**
 * PHPFrame/Application/Plugins.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Application
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * Plugins Class
 *
 * @category PHPFrame
 * @package  Application
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_Plugins extends PHPFrame_Extensions
{
    /**
     * Insert plugin info object.
     *
     * @param PHPFrame_PluginInfo $plugin_info Instance of PHPFrame_PluginInfo.
     *
     * @return void
     * @since  1.0
     */
    public function insert(PHPFrame_PluginInfo $plugin_info)
    {
        $this->mapper()->insert($plugin_info);
        $this->reload();
    }

    /**
     * Delete plugin info object.
     *
     * @param PHPFrame_PluginInfo $plugin_info Instance of PHPFrame_PluginInfo.
     *
     * @return void
     * @since  1.0
     */
    public function delete(PHPFrame_PluginInfo $plugin_info)
    {
        $this->mapper()->delete($plugin_info);
        $this->reload();
    }
}
