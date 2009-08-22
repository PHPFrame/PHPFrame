<?php
/**
 * PHPFrame/Addons/Info.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Addons
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Addon Info Abstract Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Addons
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
abstract class PHPFrame_Addons_Info extends PHPFrame_Mapper_DomainObject
{
    /**
     * The name of the addon
     * 
     * @var string
     */
    protected $name="";
    /**
     * The author
     * 
     * @var string
     */
    protected $author="";
    /**
     * Boolean indicating whether addon is enabled
     * 
     * @var bool
     */
    protected $enabled=false;
    /**
     * The addon version number
     * 
     * @var string
     */
    protected $version="";
    /**
     * The addon dependencies
     * 
     * @var PHPFrame_Addons_Dependencies
     */
    protected $dependencies;
     
    /**
     * Constructor
     * 
     * @param array $options
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        parent::__construct($options);
    }
    
    /**
     * Get name
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Set name
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setName($str)
    {
        $this->name = $str;
    }
    
    /**
     * Get author
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getAuthor()
    {
        return $this->author;
    }
    
    /**
     * Get version
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getVersion()
    {
        return $this->version;
    }
    
    /**
     * Is enabled?
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function isEnabled()
    {
        return (bool) $this->enabled;
    }
    
    /**
     * Set enabled
     * 
     * @param bool $bool
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function setEnabled($bool)
    {
        $this->enabled = (bool) $bool;
    }

    /**
     * Get dependencies
     * 
     * @access public
     * @return PHPFrame_Addons_Dependencies
     * @since  1.0
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }
    
    /**
     * Set dependencies
     * 
     * @param PHPFrame_Addons_Dependencies $bool
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setDependencies($dependencies)
    {
        $this->dependencies = $dependencies;
    }
}
