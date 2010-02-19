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
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * Extension Info Abstract Class
 * 
 * @category PHPFrame
 * @package  Ext
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
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
     * @param array $options [Optional]
     * 
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        parent::__construct($options);
    }
    
    /**
     * Implementation of IteratorAgreggate interface
     * 
     * @return ArrayIterator
     * @see PHPFrame/Mapper/PHPFrame_PersistentObject#getIterator()
     * @since  1.0
     */
    public function getIterator()
    {
        $properties = get_object_vars($this);
        
        return new ArrayIterator(array_merge($this->fields, $properties));
    }
    
    /**
     * Get name
     * 
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
     * @param string $str The extension name.
     * 
     * @return void
     * @since  1.0
     */
    public function setName($str)
    {
        $this->name = (string) $str;
    }
    
    /**
     * Get channel
     * 
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
     * @param string $str The extenion's channel.
     * 
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
     * @param string $str The extenion's summary.
     * 
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
     * @param string $str The extenion's description.
     * 
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
     * @param string $str The extenion's author.
     * 
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
     * @param string $str The extenion's build date.
     * 
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
     * @param string $str The extenion's build time.
     * 
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
     * @param array $array The extenion's version.
     * 
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
     * @param array $array The extenion's stability.
     * 
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
     * @param array $array The extenion's license.
     * 
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
     * @param string $str The extenion's release notes.
     * 
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
     * @param array $array The extenion's dependencies.
     * 
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
     * @param array $array The extenion's content array.
     * 
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
     * @param string $str Path to script.
     * 
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
     * @param string $str Path to script.
     * 
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
     * @param bool $bool Boolean indicating whether extension is enabled.
     * 
     * @return bool
     * @since  1.0
     */
    public function setEnabled($bool)
    {
        $this->enabled = (bool) $bool;
    }
}
