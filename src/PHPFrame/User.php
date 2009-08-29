<?php
/**
 * PHPFrame/User.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   User
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
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
class PHPFrame_User extends PHPFrame_Mapper_DomainObject
{
    /**
     * The group id
     * 
     * @var int
     */
    private $_groupid=0;
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
     * @var PHPFrame_Utils_vCard
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
        // If we are passed a vCard object we deal with this first
        if (
            isset($options['vcard'])
            && $options['vcard'] instanceof PHPFrame_Utils_vCard
        ) {
            $this->_vcard = $options['vcard'];
            unset($options['vcard']);
        } else {
            $this->_vcard = new PHPFrame_Utils_vCard();
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
        $int = PHPFrame_Utils_Filter::validateInt($int);
        
        $this->_groupid = $int; 
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
        $str = PHPFrame_Utils_Filter::validateRegExp($str, '/^[a-zA-Z\.]{3,20}$/');
        
        $this->_username = $str; 
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
        return $this->_username;
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
        $str = PHPFrame_Utils_Filter::validateRegExp($str, '/^.{6,100}$/');
        
        // Get random 32 char salt
        $salt = PHPFrame_Utils_Crypt::genRandomPassword(32);
        // Encrypt password using salt
        $crypt = PHPFrame_Utils_Crypt::getCryptedPassword($str, $salt);
        
        // Set password to encrypted string
        $this->_password = $crypt.':'.$salt; 
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
        $str = PHPFrame_Utils_Filter::validateRegExp($str, '/^[a-zA-Z \.\-]{1,50}$/');
        
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
        $str = PHPFrame_Utils_Filter::validateRegExp($str, '/^[a-zA-Z \.\-]{1,50}$/');
        
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
        $str = PHPFrame_Utils_Filter::validateEmail($str);
        
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
        $str = PHPFrame_Utils_Filter::validateDefault($str);
        
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
        // Set property
        $this->_block = (bool) $bool;
    }
    
    /**
     * Get last visit datetime
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getLastVisit()
    {
        return $this->_last_visit;
    }
    
    /**
     * Set last visit datetime
     * 
     * @param string $str
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setLastVisit($str)
    {
        $str = PHPFrame_Utils_Filter::validateDateTime($str);
        
        // Set property
        $this->_last_visit = $str;
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
        $str = PHPFrame_Utils_Filter::validateDefault($str);
        
        // Set property
        $this->_activation = $str;
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
        $str = PHPFrame_Utils_Filter::validateDefault($str);
        
        // Set property
        $this->_params = $str;
    }
    
    /**
     * Get deleted datetime
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getDeleted()
    {
        return $this->_deleted;
    }
    
    /**
     * Set deleted datetime
     * 
     * @param string $str
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setDeleted($str)
    {
        $str = PHPFrame_Utils_Filter::validateDateTime($str);
        
        // Set property
        $this->_deleted = $str;
    }
    
    /**
     * Get vCard object for this user
     * 
     * @access public
     * @return PHPFrame_Utils_vCard
     * @since  1.0
     */
    public function getVCard()
    {
        return $this->_vcard;
    }
    
    /**
     * Set vCard object for this user
     * 
     * @param PHPFrame_Utils_vCard $vcard
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setVCard(PHPFrame_Utils_vCard $vcard)
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
        $str = PHPFrame_Utils_Filter::validateURL($str);
        
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
        $str = PHPFrame_Utils_Filter::validateURL($str);
        
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
    public function toArray()
    {
        $properties = get_object_vars($this);
        
        foreach ($properties as $key=>$value) {
            // Ignore vCard object when rendering as array
            if ($key == "_vcard") {
                continue;
            }
            
            if ($key == "_params" || $key == "_openid_urls"
            ) {
                $value = serialize($value);
            }
            
            // Remove preceding slash if needed
            if (preg_match('/^_/', $key)) {
                $key = substr($key, 1);
            }
            
            $array[$key] = $value;
        }
        
        return $array;
    }
    
    /**
     * Is email address already registered?
     * 
     * @param string $email The email address to check
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    private function _emailExists($email) 
    {
        $sql = "SELECT id FROM #__users ";
        $sql .= " WHERE email = ':email' ";
        $sql .= " AND (deleted = '0000-00-00 00:00:00' OR deleted IS NULL) ";
        //echo str_replace("#_", "eo", $query); exit;
        //TODO: FIx me!!
        
        $id = PHPFrame::DB()->fetchColumn($sql, array(":email"=>$email));
        
        return ($id > 0);
    }
}
