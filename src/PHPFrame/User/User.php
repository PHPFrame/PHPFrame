<?php
/**
 * PHPFrame/User/User.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   User
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id: User.php 564 2009-09-01 02:50:11Z luis.montero@e-noise.com $
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * User Class
 * 
 * @category PHPFrame
 * @package  User
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_User extends PHPFrame_PersistentObject
{
    /**
     * The group id
     * 
     * @var int
     */
    private $_groupid=0;
    /**
     * Text label for groupid. This is not stored in the users db table and is 
     * instead retrieved by joining with the groups table.
     * 
     * @var string
     */
    private $_groupname=null;
    /**
     * Username
     * 
     * @var string
     */
    private $_username=null;
    /**
     * Password
     * 
     * @var string
     */
    private $_password=null;
    /**
     * First name
     * 
     * @var string
     */
    private $_firstname=null;
    /**
     * Last name
     * 
     * @var string
     */
    private $_lastname=null;
    /**
     * Email
     * 
     * @var string
     */
    private $_email=null;
    /**
     * Photo
     * 
     * @var string
     */
    private $_photo=null;
    /**
     * Flag to indicate whether user will reveive email notifications
     * 
     * @var bool
     */
    private $_notifications=true;
    /**
     * Flag to indicate whether user's email will be shown in front-end
     * 
     * @var bool
     */
    private $_show_email=true;
    /**
     * Flag to indicate whether the user account has been blocked by an admin
     * 
     * @var bool
     */
    private $_block=false;
    /**
     * Date the user last visited the app (in MySQL Datetime format)
     * 
     * @var string
     */
    private $_last_visit=null;
    /**
     * Activation key
     * 
     * @var string
     */
    private $_activation=null;
    /**
     * User params
     * 
     * @var array
     */
    private $_params=array();
    /**
     * Date the user was deleted (in MySQL Datetime format)
     * 
     * This field is empty for all active users
     * 
     * @var string
     */
    private $_deleted=null;
    /**
     * vCard object used to store user details 
     * 
     * @var PHPFrame_vCard
     */
    private $_vcard=null;
    /**
     * An array containig openid urls linked to this user
     * 
     * @var array
     */
    private $_openid_urls=array();
    
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
        $this->addFilter("groupid", "int");
        $this->addFilter("username","varchar", 20, 3, false, null, '/^[a-zA-Z\.]{3,20}$/');
        $this->addFilter("password", "varchar", 100, 6, false, null, '/^.{6,100}$/');
        $this->addFilter("firstname", "varchar", 50, 1, false, null, '/^[a-zA-Z \.\-]{1,50}$/');
        $this->addFilter("lastname", "varchar", 50, 1, false, null, '/^[a-zA-Z \.\-]{1,50}$/');
        $this->addFilter("email", "varchar", 100, 7, false, null, 'email');
        $this->addFilter("photo", "varchar", 128, 1, false, "default.png");
        $this->addFilter("notifications", "enum", array(0,1), null, false, 1);
        $this->addFilter("show_email", "enum", array(0,1), null, false, 0);
        $this->addFilter("block", "enum", array(0,1), null, false, 0);
        $this->addFilter("last_visit", "int", null, null, false, 0);
        $this->addFilter("activation", "varchar", 100, null, true);
        $this->addFilter("params", "text", null, null, true);
        $this->addFilter("deleted", "int", null, null, true);
        $this->addFilter("openid_urls", "text", null, null, true);
        
        // If we are passed a vCard object we deal with this first
        if (
            isset($options['vcard'])
            && $options['vcard'] instanceof PHPFrame_vCard
        ) {
            $this->_vcard = $options['vcard'];
            unset($options['vcard']);
        } else {
            $this->_vcard = new PHPFrame_vCard();
        }
        
        // Once we have set the vCard object we call the parent's constructor
        // to process the options array
        parent::__construct($options);
    }
    
    /**
     * Get groupid
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function getGroupId()
    {
        return $this->_groupid;
    }
    
    /**
     * Set groupid
     * 
     * @param int $int
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setGroupId($int)
    {
        $this->_groupid = $this->validate("groupid", $int);
    }
    
    /**
     * Get groupname
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getGroupName()
    {
        return $this->_groupname;
    }
    
    /**
     * Set groupname
     * 
     * @param string $str
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setGroupName($str)
    {
        $this->_groupname = trim((string) $str);
    }
    
    /**
     * Get username
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getUserName()
    {
        return $this->_username;
    }
    
    /**
     * Set username
     * 
     * @param string $str
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setUserName($str)
    {
        $this->_username = $this->validate("username", $str);
    }
    
    /**
     * Get password
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getPassword()
    {
        return $this->_password;
    }
    
    /**
     * Set password
     * 
     * @param string $str
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setPassword($str)
    {
        $this->_password = $this->validate("password", $str);
    }
    
    /**
     * Create enrypted password
     * 
     * @param string $str
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function encryptPassword($str)
    {
        $str = $this->validate("password", $str);
        
        // Get random 32 char salt
        $salt = PHPFrame_Crypt::genRandomPassword(32);
        // Encrypt password using salt
        $crypt = PHPFrame_Crypt::getCryptedPassword($str, $salt);
        
        // Set password to encrypted string
        return $crypt.':'.$salt;
    }
    
    /**
     * Get first name
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getFirstName()
    {
        return $this->_vcard->getName("FIRST");
    }
    
    /**
     * Set first name
     * 
     * @param string $str
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setFirstName($str)
    {
        $str = $this->validate("firstname", $str);
        
        // Set first name in vCard object making sure we dont overwrite last name)
        $this->_vcard->setName($this->getLastName(), $str, null, null, null);
        
        // Set property
        $this->_firstname = $str;
    }
    
    /**
     * Get last name
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getLastName()
    {
        return $this->_vcard->getName("LAST");
    }
    
    /**
     * Set last name
     * 
     * @param string $str
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setLastName($str)
    {
        $str = $this->validate("lastname", $str);
        
        // Set last name in vCard object making sure we dont overwrite first name)
        $this->_vcard->setName($str, $this->getFirstName(), null, null, null);
        
        // Set property
        $this->_lastname = $str;
    }
    
    /**
     * Get email
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getEmail()
    {
        return $this->_email;
    }

    /**
     * Set email
     * 
     * @param string $str
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setEmail($str)
    {
        $str = $this->validate("email", $str);
        
        // Set last name in vCard object making sure we dont overwrite first name)
        $this->_vcard->setEmail($str);
        
        // Set property
        $this->_email = $str;
    }
    
    /**
     * Get photo
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getPhoto()
    {
        return $this->_photo;
    }
    
    /**
     * Set photo
     * 
     * @param string $str
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setPhoto($str)
    {
        $str = $this->validate("photo", $str);
        
        // Set last name in vCard object making sure we dont overwrite first name)
        $this->_vcard->setPhoto($str);
        
        // Set property
        $this->_photo = $str;
    }
    
    /**
     * Get notifications flag
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function getNotifications()
    {
        return $this->_notifications;
    }
    
    /**
     * Set notifications flag
     * 
     * @param bool $bool
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setNotifications($bool)
    {
        $bool = $this->validate("notifications", $bool);
        
        // Set local property
        $this->_notifications = (bool) $bool;
    }
    
    /**
     * Get show_email flag
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function getShowEmail()
    {
        return $this->_show_email;
    }
    
    /**
     * Set show_email flag
     * 
     * @param bool $bool
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setShowEmail($bool)
    {
        $bool = $this->validate("show_email", $bool);
        
        // Set property
        $this->_show_email = (bool) $bool;
    }
    
    /**
     * Get block flag
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function getBlock()
    {
        return $this->_block;
    }
    
    /**
     * Set block flag
     * 
     * @param bool $bool
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setBlock($bool)
    {
        $bool = $this->validate("block", $bool);
        
        // Set property
        $this->_block = (bool) $bool;
    }
    
    /**
     * Get last visit timestamp
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function getLastVisit()
    {
        return $this->_last_visit;
    }
    
    /**
     * Set last visit datetime
     * 
     * @param int $int
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setLastVisit($int)
    {
        $this->_last_visit = $this->validate("last_visit", $int);
    }
    
    /**
     * Get activation key
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getActivation()
    {
        return $this->_activation;
    }
    
    /**
     * Set activation key
     * 
     * @param string $str
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setActivation($str)
    {
        $this->_activation = $this->validate("activation", $str);
    }
    
    /**
     * Get params
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getParams()
    {
        return $this->_params;
    }
    
    /**
     * Set params
     * 
     * @param string $str
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setParams($str)
    {
        $this->_params = $this->validate("params", $str);
    }
    
    /**
     * Get deleted timestamp
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function getDeleted()
    {
        return $this->_deleted;
    }
    
    /**
     * Set deleted datetime
     * 
     * @param int $int
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setDeleted($int)
    {
        if (empty($int)) {
            return;
        }
        
        $this->_deleted = $this->validate("deleted", $int);
    }
    
    /**
     * Get vCard object for this user
     * 
     * @access public
     * @return PHPFrame_vCard
     * @since  1.0
     */
    public function getVCard()
    {
        return $this->_vcard;
    }
    
    /**
     * Set vCard object for this user
     * 
     * @param PHPFrame_vCard $vcard
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setVCard(PHPFrame_vCard $vcard)
    {
        //TODO: Here we have to parse the vCard data 
        // and update the firstname and lastname proerties
        $this->_vcard = $vcard;
    }
    
    /**
     * Get OpenId URLs
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getOpenidUrls()
    {
        return $this->_openid_urls;
    }
    
    /**
     * Add an OpenId URL for this user
     * 
     * @param string $str
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function addOpenidUrl($str)
    {
        $str = $this->validate("openid_urls", $str);
        
        if (!in_array($str, $this->_openid_urls)) {
            $this->_openid_urls[] = $str;
        }
    }
    
    /**
     * Remove a given URL from the openid_urls array
     * 
     * @param string $str
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function removeOpenidUrl($str)
    {
        $str = $this->validate("openid_urls", $str);
        
        foreach ($this->_openid_urls as $url) {
            if ($str != $url) {
                $array[] = $url;
            }
        }
        
        $this->_openid_urls = $array;
    }
    
    /**
     * Return Row object as associative array
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getIterator()
    {
        $properties = get_object_vars($this);
        
        foreach ($properties as $key=>$value) {
            // Ignore vCard object when rendering as array
            if ($key == "_vcard" || $key == "_groupname") {
                continue;
            }
            
            if ($key == "_params" || $key == "_openid_urls") {
                $value = serialize($value);
            }
            
            // Remove preceding slash if needed
            if (preg_match('/^_/', $key)) {
                $key = substr($key, 1);
            }
            
            $array[$key] = $value;
        }
        
        return new ArrayIterator($array);
    }
}
