<?php
/**
 * PHPFrame/Application/Libraries.php
 * 
 * PHP version 5
 * 
 * @category PHPFrame
 * @package    PHPFrame
 * @subpackage Application
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Application Libraries Class
 * 
 * @category PHPFrame
 * @package    PHPFrame
 * @subpackage Application
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_Application_Libraries
{
    /**
     * A mapper object used to store and retrieve libraries info
     *
     * @var PHPFrame_Mapper_Collection
     */
    private $_mapper;
    /**
     * A collection object holding libraries info
     *
     * @var PHPFrame_Mapper_Collection
     */
    private $_libs;
    
    /**
     * Constructor
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct() 
    {
        // Get installed libraries from file
        $this->_mapper = new PHPFrame_Mapper(
            "PHPFrame_Addons_LibInfo", 
            "lib", 
            PHPFrame_Mapper::STORAGE_XML, 
            false, 
            PHPFRAME_CONFIG_DIR
        );
        
        $this->_libs = $this->_mapper->find();
    }
}
