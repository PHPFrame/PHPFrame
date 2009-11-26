<?php
/**
 * PHPFrame/Ext/ExtInfo.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Ext
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * Extension Info Abstract Class
 * 
 * @category PHPFrame
 * @package  Ext
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since    1.0
 */
abstract class PHPFrame_ExtInfo extends PHPFrame_PersistentObject
{
    /**
     * The name of the addon
     * 
     * @var string
     */
    protected $name;
    /**
     * The dist channel
     * 
     * @var string
     */
    protected $channel;
    /**
     * The summary
     * 
     * @var string
     */
    protected $summary;
    /**
     * The description
     * 
     * @var string
     */
    protected $description;
    /**
     * The author
     * 
     * @var string
     */
    protected $author;
    /**
     * The release date
     * 
     * @var string
     */
    protected $date;
    /**
     * The release time
     * 
     * @var string
     */
    protected $time;
    /**
     * The release and api version
     * 
     * @var array
     */
    protected $version = array("release"=>null, "api"=>null);
    /**
     * Stability info for release and api (alpha, beta or stable)
     * 
     * @var array
     */
    protected $stability = array("release"=>null, "api"=>null);
    /**
     * License name and URI
     * 
     * @var array
     */
    protected $license = array("name"=>null, "uri"=>null);
    /**
     * Notes
     * 
     * @var string
     */
    protected $notes;
    /**
     * The addon dependencies
     * 
     * @var array
     */
    protected $dependencies = array("required"=>array(), "optional"=>array());
    /**
     * Package contents
     * 
     * @var array
     */
    protected $contents = array();
    /**
     * Boolean indicating whether addon is enabled
     * 
     * @var bool
     */
    protected $enabled=false;
     
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
    
    public function getIterator()
    {
        $properties = get_object_vars($this);
        
        return new ArrayIterator(array_merge($this->fields, $properties));
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
     * Get channel
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getChannel()
    {
        return $this->channel;
    }
    
    /**
     * Set channel
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setChannel($str)
    {
        $this->channel = $str;
    }
    
    /**
     * Get summary
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getSummary()
    {
        return $this->summary;
    }
    
    /**
     * Set summary
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setSummary($str)
    {
        $this->summary = $str;
    }
    
    /**
     * Get description
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * Set description
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setDescription($str)
    {
        $this->description = $str;
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
     * Set author
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setAuthor($str)
    {
        $this->author = $str;
    }
    
    /**
     * Get date
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getDate()
    {
        return $this->date;
    }
    
    /**
     * Set date
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setDate($str)
    {
        $this->date = $str;
    }
    
    /**
     * Get time
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getTime()
    {
        return $this->time;
    }
    
    /**
     * Set time
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setTime($str)
    {
        $this->time = $str;
    }
    
    /**
     * Get release version
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getReleaseVersion()
    {
        return $this->version["release"];
    }
    
    /**
     * Set version
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setVersion(array $array)
    {
        $this->version = $array;
    }
    
    /**
     * Set stability
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setStability(array $array)
    {
        $this->stability = $array;
    }
    
    /**
     * Set license
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setLicense(array $array)
    {
        $this->license = $array;
    }
    
    /**
     * Set notes
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setNotes($str)
    {
        $this->notes = $str;
    }
    
    /**
     * Get dependencies
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }
    
    /**
     * Set dependencies
     * 
     * @param array $array
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setDependencies(array $array)
    {
        $this->dependencies = $array;
    }
    
    /**
     * Get contents
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getContents()
    {
        return $this->contents;
    }
    
    /**
     * Add content
     * 
     * @param array $array
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setContents(array $array)
    {
        $this->contents = $array;
    }
    
    /**
     * Get install scripts
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getInstallScripts()
    {
        return $this->install;
    }
    
    /**
     * Add install script
     * 
     * @param string $str
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function addInstallScript($str)
    {
        $this->install[] = array("path"=>$str, "role"=>"php");
    }
    
    /**
     * Get uninstall scripts
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getUninstallScripts()
    {
        return $this->uninstall;
    }
    
    /**
     * Add uninstall script
     * 
     * @param string $str
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function addUninstallScript($str)
    {
        $this->uninstall[] = array("path"=>$str, "role"=>"php");
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
}
