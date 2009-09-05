<?php
/**
 * PHPFrame/Application/Features.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Application
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Features Class
 * 
 * @category PHPFrame
 * @package  Application
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_Application_Features
{
    /**
     * A mapper object used to store and retrieve feature data
     *
     * @var PHPFrame_Mapper_Collection
     */
    private $_mapper;
    /**
     * A collection object holding data about installed features
     *
     * @var PHPFrame_Mapper_Collection
     */
    private $_features;
    
    /**
     * Construct
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct() 
    {
        // Get installed features from file
        $this->_mapper = new PHPFrame_Mapper(
            "PHPFrame_Addons_FeatureInfo", 
            "features", 
            PHPFrame_Mapper::STORAGE_XML, 
            false, 
            PHPFRAME_CONFIG_DIR
        );
        
        $this->_features = $this->_mapper->find();
    }
    
    public function install($name)
    {
        //$this->_mapper->insert(new PHPFrame_Addons_FeatureInfo());
    }
    
    public function uninstall($name)
    {
        
    }
    
    /**
     * Get feature info by name
     * 
     * @param string $name The feature name.
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getInfo($name) 
    {
        foreach ($this->_features as $feature) {
            if ($feature->getName() == $name) {
                return $feature;
            }
        }
        
        $msg = "Feature '".$name."' is not installed";
        throw new RuntimeException($msg);
    }
    
    /**
     * This methods tests whether the specified feature is installed and enabled.
     *
     * @param string $name The feature name to check (ie: dashboard, user, 
     *                     projects, ...)
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function isEnabled($name) 
    {
        foreach ($this->_features as $feature) {
            if ($feature->getName() == $name && $feature->isEnabled()) {
                return true;
            }
        }
        
        return false;
    }
    
    public function isInstalled($name)
    {
        foreach ($this->_features as $feature) {
            if ($feature->getName() == $name && $feature->isInstalled()) {
                return true;
            }
        }
        
        return false;
    }
}
